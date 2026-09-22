<?php

namespace App\Services\Gamifikasi;

use App\Events\PointsEarned;
use App\Models\HasilKuis;
use App\Models\JawabanSiswa;
use App\Models\Materi;
use App\Models\PointsLog;
use App\Models\ProgresMateri;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu masuk perubahan saldo poin siswa. Seluruh mutasi selalu
 * berpasangan: satu baris points_log + penyesuaian users.total_poin, di dalam
 * transaksi yang sama sehingga saldo tidak pernah menyimpang dari riwayat.
 */
class PoinService
{
    public function __construct(private LevelService $levelService)
    {
    }

    /**
     * Menambah (atau mengurangi, bila $jumlah negatif) poin siswa.
     *
     * $kunciUnik membuat pemberian poin bersifat idempoten: memanggil ulang
     * dengan kunci yang sama tidak menambah poin untuk kedua kalinya.
     * Kunci null dipakai untuk mutasi yang memang boleh berulang.
     */
    public function catat(User $user, string $sumber, int $jumlah, string $keterangan, ?string $kunciUnik = null): ?PointsLog
    {
        if ($jumlah === 0) {
            return null;
        }

        $hasil = DB::transaction(function () use ($user, $sumber, $jumlah, $keterangan, $kunciUnik) {
            // Baris siswa dikunci lebih dulu agar dua pemberian poin yang
            // berbarengan tidak saling menimpa saldo.
            $terkunci = User::whereKey($user->id)->lockForUpdate()->first();

            if ($kunciUnik !== null) {
                $sudahAda = PointsLog::where('user_id', $user->id)
                    ->where('kunci_unik', $kunciUnik)
                    ->exists();

                if ($sudahAda) {
                    return null;
                }
            }

            $log = PointsLog::create([
                'user_id' => $user->id,
                'sumber' => $sumber,
                'jumlah' => $jumlah,
                'keterangan' => $keterangan,
                'kunci_unik' => $kunciUnik,
            ]);

            // Kolom total_poin unsigned, jadi saldo ditahan pada batas bawah 0.
            $saldoBaru = max(0, $terkunci->total_poin + $jumlah);
            $terkunci->forceFill(['total_poin' => $saldoBaru])->save();

            // Instance pemanggil ikut disegarkan supaya pembacaan setelah ini akurat.
            $user->forceFill(['total_poin' => $saldoBaru])->syncOriginalAttribute('total_poin');

            return $log;
        });

        if (! $hasil) {
            return null;
        }

        $levelNaik = $this->levelService->selaraskan($user);

        if ($hasil->jumlah > 0) {
            PointsEarned::dispatch($user, $hasil, $levelNaik);
        }

        return $hasil;
    }

    /**
     * Poin membaca materi, ditambah bonus bila seluruh materi pada babnya tuntas.
     *
     * @return Collection<int, PointsLog>
     */
    public function dariMateri(User $user, Materi $materi): Collection
    {
        $log = collect();

        $poinMateri = $this->catat(
            $user,
            'materi',
            $materi->poinGamifikasi(),
            "Menyelesaikan materi: {$materi->judul}",
            "materi:{$materi->id}",
        );

        if ($poinMateri) {
            $log->push($poinMateri);
        }

        if ($bonusBab = $this->bonusBabSelesai($user, $materi)) {
            $log->push($bonusBab);
        }

        return $log;
    }

    /**
     * Poin kuis dijumlahkan dari poin tiap soal yang dijawab benar, ditambah
     * bonus lulus KKM dan bonus ketepatan waktu pengumpulan. Soal
     * troubleshooting menyumbang poin sebagian sesuai porsi langkah yang tepat.
     *
     * @return Collection<int, PointsLog>
     */
    public function dariKuis(User $user, HasilKuis $hasilKuis): Collection
    {
        $log = collect();
        $kuis = $hasilKuis->kuis ?? $hasilKuis->kuis()->first();

        if (! $kuis || $hasilKuis->skor === null) {
            return $log;
        }

        $skor = (float) $hasilKuis->skor;
        $poin = $this->poinDariJawaban($hasilKuis);

        // Kunci memakai id kuis, bukan id percobaan: poin kuis diberikan sekali
        // saja. Tanpa ini siswa bisa memanen poin dengan mengulang kuis yang sama.
        if ($poinKuis = $this->catat($user, 'kuis', $poin, "Mengerjakan kuis: {$kuis->judul} (skor {$skor})", "kuis:{$kuis->id}")) {
            $log->push($poinKuis);
        }

        if ($skor >= $kuis->kkm) {
            $bonus = $this->catat(
                $user,
                'bonus_kkm',
                (int) config('gamifikasi.poin.bonus_lulus_kkm'),
                "Lulus KKM pada kuis: {$kuis->judul}",
                "bonus_kkm:{$kuis->id}",
            );

            if ($bonus) {
                $log->push($bonus);
            }
        }

        // Bonus ketepatan waktu hanya untuk pengerjaan yang benar-benar dikerjakan,
        // supaya submit kosong secepat mungkin tidak berbuah poin.
        if ($skor > 0 && $this->tepatWaktu($hasilKuis, $kuis->durasi_menit)) {
            $bonus = $this->catat(
                $user,
                'bonus_tepat_waktu',
                (int) config('gamifikasi.poin.bonus_tepat_waktu'),
                "Mengumpulkan kuis tepat waktu: {$kuis->judul}",
                "bonus_tepat_waktu:{$kuis->id}",
            );

            if ($bonus) {
                $log->push($bonus);
            }
        }

        return $log;
    }

    /**
     * Mencatat kehadiran harian dan memberi bonus streak. Dipanggil saat login
     * sehingga siswa yang belajar tiap hari mendapat poin tanpa aksi tambahan.
     */
    public function catatStreak(User $user): ?PointsLog
    {
        $hariIni = now()->startOfDay();
        $terakhir = $user->streak_terakhir?->startOfDay();

        if ($terakhir && $terakhir->equalTo($hariIni)) {
            return null;
        }

        $streak = $terakhir && $terakhir->equalTo($hariIni->copy()->subDay())
            ? $user->streak_hari + 1
            : 1;

        $user->forceFill([
            'streak_hari' => $streak,
            'streak_terakhir' => $hariIni,
        ])->save();

        $jumlah = min($streak, (int) config('gamifikasi.poin.streak_maksimal_hari'))
            * (int) config('gamifikasi.poin.streak_per_hari');

        return $this->catat(
            $user,
            'streak',
            $jumlah,
            "Bonus streak hari ke-{$streak}",
            'streak:'.$hariIni->toDateString(),
        );
    }

    /**
     * Penyesuaian manual oleh guru/admin, mis. koreksi atau apresiasi khusus.
     */
    public function penyesuaian(User $user, int $jumlah, string $keterangan): ?PointsLog
    {
        return $this->catat($user, 'penyesuaian', $jumlah, $keterangan);
    }

    /**
     * Menjumlahkan poin dari jawaban yang benar. Soal essay tidak menyumbang
     * poin otomatis karena penilaiannya tidak dilakukan server.
     */
    private function poinDariJawaban(HasilKuis $hasilKuis): int
    {
        $jawaban = $hasilKuis->relationLoaded('jawabanSiswa')
            ? $hasilKuis->jawabanSiswa
            : $hasilKuis->jawabanSiswa()->with('soal')->get();

        return (int) $jawaban->sum(function (JawabanSiswa $item) {
            $soal = $item->soal;

            if (! $soal || $soal->tipe === 'essay') {
                return 0;
            }

            $porsi = $soal->isTroubleshooting()
                ? (float) ($item->porsi_benar ?? 0)
                : ($item->is_benar ? 1.0 : 0.0);

            return (int) round($soal->poinGamifikasi() * $porsi);
        });
    }

    private function bonusBabSelesai(User $user, Materi $materi): ?PointsLog
    {
        $bab = $materi->bab ?? $materi->bab()->first();

        if (! $bab) {
            return null;
        }

        $materiIds = $bab->materi()->pluck('id');

        if ($materiIds->isEmpty()) {
            return null;
        }

        $selesai = ProgresMateri::where('user_id', $user->id)
            ->selesai()
            ->whereIn('materi_id', $materiIds)
            ->count();

        if ($selesai < $materiIds->count()) {
            return null;
        }

        return $this->catat(
            $user,
            'bonus_bab',
            (int) config('gamifikasi.poin.bonus_bab_selesai'),
            "Menuntaskan seluruh materi bab: {$bab->judul}",
            "bonus_bab:{$bab->id}",
        );
    }

    private function tepatWaktu(HasilKuis $hasilKuis, int $durasiMenit): bool
    {
        if (! $hasilKuis->waktu_mulai || ! $hasilKuis->waktu_selesai || $durasiMenit <= 0) {
            return false;
        }

        $terpakai = $hasilKuis->waktu_mulai->diffInSeconds($hasilKuis->waktu_selesai);
        $batas = $durasiMenit * 60 * (float) config('gamifikasi.ketepatan_waktu.rasio_durasi');

        return $terpakai <= $batas;
    }
}
