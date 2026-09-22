<?php

namespace App\Services\Gamifikasi;

use App\Models\User;

/**
 * Menerjemahkan total poin menjadi level bertema TKJ beserta progres menuju
 * level berikutnya. Daftar level dibaca dari config/gamifikasi.php.
 */
class LevelService
{
    /**
     * @return array<int, array{nama: string, minimal_poin: int, ikon: string}>
     */
    public function daftar(): array
    {
        $level = config('gamifikasi.level', []);

        usort($level, fn ($a, $b) => $a['minimal_poin'] <=> $b['minimal_poin']);

        return $level;
    }

    public function levelUntuk(int $totalPoin): array
    {
        $terpilih = $this->daftar()[0];

        foreach ($this->daftar() as $level) {
            if ($totalPoin >= $level['minimal_poin']) {
                $terpilih = $level;
            }
        }

        return $terpilih;
    }

    public function levelBerikutnya(int $totalPoin): ?array
    {
        foreach ($this->daftar() as $level) {
            if ($totalPoin < $level['minimal_poin']) {
                return $level;
            }
        }

        return null;
    }

    /**
     * Ringkasan level untuk ditampilkan pada progress bar siswa.
     */
    public function progres(int $totalPoin): array
    {
        $saatIni = $this->levelUntuk($totalPoin);
        $berikutnya = $this->levelBerikutnya($totalPoin);

        if (! $berikutnya) {
            return [
                'level' => $saatIni['nama'],
                'ikon' => $saatIni['ikon'],
                'level_berikutnya' => null,
                'poin_ke_level_berikutnya' => 0,
                'persentase' => 100,
            ];
        }

        $rentang = $berikutnya['minimal_poin'] - $saatIni['minimal_poin'];
        $kemajuan = $totalPoin - $saatIni['minimal_poin'];

        return [
            'level' => $saatIni['nama'],
            'ikon' => $saatIni['ikon'],
            'level_berikutnya' => $berikutnya['nama'],
            'poin_ke_level_berikutnya' => $berikutnya['minimal_poin'] - $totalPoin,
            'persentase' => $rentang > 0 ? (int) round($kemajuan / $rentang * 100) : 0,
        ];
    }

    /**
     * Menyelaraskan kolom users.level dengan total poin terkini.
     * Mengembalikan true bila terjadi kenaikan level.
     */
    public function selaraskan(User $user): bool
    {
        $levelBaru = $this->levelUntuk($user->total_poin)['nama'];

        if ($levelBaru === $user->level) {
            return false;
        }

        $indeksLama = $this->indeks($user->level);
        $user->forceFill(['level' => $levelBaru])->save();

        return $this->indeks($levelBaru) > $indeksLama;
    }

    private function indeks(?string $nama): int
    {
        foreach ($this->daftar() as $i => $level) {
            if ($level['nama'] === $nama) {
                return $i;
            }
        }

        return -1;
    }
}
