<?php

namespace App\Services\Gamifikasi;

use App\Models\PointsLog;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Peringkat dihitung dari points_log, bukan dari users.total_poin, supaya
 * periode mingguan/bulanan/semester benar-benar mencerminkan perolehan pada
 * rentang tersebut dan tidak terpengaruh penukaran reward.
 */
class LeaderboardService
{
    public const PERIODE = ['mingguan', 'bulanan', 'semester'];

    public const LINGKUP = ['kelas', 'angkatan', 'global'];

    public function awalPeriode(string $periode): CarbonInterface
    {
        return match ($periode) {
            'mingguan' => now()->startOfWeek(),
            'bulanan' => now()->startOfMonth(),
            // Semester ganjil dimulai Juli, semester genap dimulai Januari.
            default => now()->month >= 7
                ? now()->startOfYear()->addMonths(6)
                : now()->startOfYear(),
        };
    }

    /**
     * @return Collection<int, array{peringkat: int, user_id: int, nama: string, kelas: ?string, tingkat: ?int, poin: int, level: string}>
     */
    public function peringkat(string $periode, string $lingkup, ?User $konteks = null, ?int $kelasId = null, ?int $tingkat = null, int $limit = 50): Collection
    {
        $kelasId ??= $konteks?->kelas_id;
        $tingkat ??= $konteks?->kelas?->tingkat;

        $query = PointsLog::query()
            ->join('users', 'users.id', '=', 'points_log.user_id')
            ->leftJoin('kelas', 'kelas.id', '=', 'users.kelas_id')
            ->where('users.role', 'siswa')
            ->perolehan()
            ->where('points_log.created_at', '>=', $this->awalPeriode($periode))
            ->groupBy('users.id', 'users.name', 'users.level', 'kelas.nama_rombel', 'kelas.tingkat')
            ->select(
                'users.id as user_id',
                'users.name as nama',
                'users.level as level',
                'kelas.nama_rombel as kelas',
                'kelas.tingkat as tingkat',
                DB::raw('SUM(points_log.jumlah) as poin'),
            )
            ->orderByDesc('poin')
            ->orderBy('users.name');

        if ($lingkup === 'kelas' && $kelasId) {
            $query->where('users.kelas_id', $kelasId);
        }

        if ($lingkup === 'angkatan' && $tingkat) {
            $query->where('kelas.tingkat', $tingkat);
        }

        return $query->limit($limit)->get()->values()->map(fn ($baris, $i) => [
            'peringkat' => $i + 1,
            'user_id' => (int) $baris->user_id,
            'nama' => $baris->nama,
            'kelas' => $baris->kelas,
            'tingkat' => $baris->tingkat === null ? null : (int) $baris->tingkat,
            'level' => $baris->level,
            'poin' => (int) $baris->poin,
        ]);
    }

    /**
     * Posisi siswa tertentu pada papan peringkat, atau null bila belum masuk daftar.
     */
    public function posisi(Collection $peringkat, int $userId): ?array
    {
        return $peringkat->firstWhere('user_id', $userId);
    }
}
