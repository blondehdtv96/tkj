<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HasilKuis;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\ProgresMateri;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => [
                'rata_rata_per_kelas' => $this->rataRataPerKelas(),
                'rata_rata_per_bab' => $this->rataRataPerBab(),
                'siswa_di_bawah_kkm' => $this->siswaDiBawahKkm(),
                'progres_materi' => $this->progresMateri(),
            ],
        ]);
    }

    private function rataRataPerKelas(): array
    {
        return HasilKuis::query()
            ->join('users', 'users.id', '=', 'hasil_kuis.user_id')
            ->join('kelas', 'kelas.id', '=', 'users.kelas_id')
            ->whereNotNull('hasil_kuis.waktu_selesai')
            ->groupBy('kelas.id', 'kelas.nama_rombel')
            ->select('kelas.id as kelas_id', 'kelas.nama_rombel', DB::raw('ROUND(AVG(hasil_kuis.skor), 2) as rata_rata'), DB::raw('COUNT(*) as jumlah_kuis'))
            ->get()
            ->toArray();
    }

    private function rataRataPerBab(): array
    {
        return HasilKuis::query()
            ->join('kuis', 'kuis.id', '=', 'hasil_kuis.kuis_id')
            ->join('bab', 'bab.id', '=', 'kuis.bab_id')
            ->whereNotNull('hasil_kuis.waktu_selesai')
            ->groupBy('bab.id', 'bab.judul')
            ->select('bab.id as bab_id', 'bab.judul', DB::raw('ROUND(AVG(hasil_kuis.skor), 2) as rata_rata'), DB::raw('COUNT(*) as jumlah_kuis'))
            ->get()
            ->toArray();
    }

    private function siswaDiBawahKkm(): array
    {
        return HasilKuis::query()
            ->join('users', 'users.id', '=', 'hasil_kuis.user_id')
            ->join('kuis', 'kuis.id', '=', 'hasil_kuis.kuis_id')
            ->whereNotNull('hasil_kuis.waktu_selesai')
            ->whereColumn('hasil_kuis.skor', '<', 'kuis.kkm')
            ->select('users.name as nama_siswa', 'kuis.judul as kuis', 'hasil_kuis.skor', 'kuis.kkm')
            ->orderBy('hasil_kuis.skor')
            ->limit(50)
            ->get()
            ->toArray();
    }

    private function progresMateri(): array
    {
        $totalMateriPerTingkat = Materi::join('bab', 'bab.id', '=', 'materi.bab_id')
            ->join('mata_pelajaran', 'mata_pelajaran.id', '=', 'bab.mata_pelajaran_id')
            ->select('mata_pelajaran.tingkat', DB::raw('COUNT(*) as total_materi'))
            ->groupBy('mata_pelajaran.tingkat')
            ->pluck('total_materi', 'tingkat');

        return Kelas::withCount('siswa')->get()->map(function (Kelas $kelas) use ($totalMateriPerTingkat) {
            $totalMateri = $totalMateriPerTingkat[$kelas->tingkat] ?? 0;
            $totalSelesai = ProgresMateri::whereHas('user', fn ($q) => $q->where('kelas_id', $kelas->id))->count();
            $totalMungkin = $totalMateri * $kelas->siswa_count;

            return [
                'kelas_id' => $kelas->id,
                'nama_rombel' => $kelas->nama_rombel,
                'persentase' => $totalMungkin > 0 ? round(($totalSelesai / $totalMungkin) * 100, 1) : 0,
            ];
        })->toArray();
    }
}
