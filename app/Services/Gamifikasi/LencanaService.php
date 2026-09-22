<?php

namespace App\Services\Gamifikasi;

use App\Models\HasilKuis;
use App\Models\PointsLog;
use App\Models\ProgresMateri;
use App\Models\Redemption;
use App\Models\User;

/**
 * Lencana dihitung ulang dari data setiap kali diminta, bukan disimpan.
 * Dengan begitu tidak ada lencana basi ketika nilai atau progres berubah.
 */
class LencanaService
{
    /**
     * @return array<int, array{kode: string, nama: string, deskripsi: string, ikon: string, diraih: bool, progres: int, target: int}>
     */
    public function untuk(User $user): array
    {
        $metrik = $this->metrik($user);

        return array_map(function (array $lencana) use ($metrik) {
            $nilaiSaatIni = $metrik[$lencana['metrik']] ?? 0;

            return [
                'kode' => $lencana['kode'],
                'nama' => $lencana['nama'],
                'deskripsi' => $lencana['deskripsi'],
                'ikon' => $lencana['ikon'],
                'diraih' => $nilaiSaatIni >= $lencana['nilai'],
                'progres' => min($nilaiSaatIni, $lencana['nilai']),
                'target' => $lencana['nilai'],
            ];
        }, config('gamifikasi.lencana', []));
    }

    /**
     * @return array<string, int>
     */
    public function metrik(User $user): array
    {
        $kuisSelesai = HasilKuis::with('kuis')
            ->where('user_id', $user->id)
            ->whereNotNull('waktu_selesai')
            ->get();

        return [
            'materi_selesai' => ProgresMateri::where('user_id', $user->id)->selesai()->count(),
            'kuis_lulus' => $kuisSelesai->filter(fn (HasilKuis $h) => $h->skor !== null && $h->kuis && $h->skor >= $h->kuis->kkm)->count(),
            'nilai_sempurna' => $kuisSelesai->filter(fn (HasilKuis $h) => (float) $h->skor === 100.0)->count(),
            'streak_hari' => (int) $user->streak_hari,
            'poin_seumur_hidup' => (int) PointsLog::where('user_id', $user->id)->perolehan()->sum('jumlah'),
            'reward_ditukar' => Redemption::where('user_id', $user->id)->whereIn('status', ['approved', 'completed'])->count(),
        ];
    }
}
