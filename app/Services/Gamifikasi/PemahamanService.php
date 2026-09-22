<?php

namespace App\Services\Gamifikasi;

use App\Models\Materi;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Pengecek kuis pemahaman singkat sebelum materi boleh ditandai selesai,
 * supaya poin membaca tidak bisa didapat dengan sekadar menekan tombol.
 *
 * Materi yang belum memiliki soal pemahaman tetap bisa langsung ditandai
 * selesai, sehingga konten lama tidak ikut terkunci.
 */
class PemahamanService
{
    /**
     * @param  array<int, array{soal_id: int, jawaban: int}>  $jawaban
     * @return array{total: int, benar: int}
     */
    public function periksa(Materi $materi, array $jawaban): array
    {
        $soal = $materi->soalPemahaman()->get();

        if ($soal->isEmpty()) {
            return ['total' => 0, 'benar' => 0];
        }

        $terjawab = collect($jawaban)->keyBy('soal_id');

        $belumDijawab = $soal->reject(fn ($item) => $terjawab->has($item->id));

        if ($belumDijawab->isNotEmpty()) {
            throw ValidationException::withMessages([
                'jawaban' => 'Jawab dulu seluruh soal pemahaman materi ini sebelum menandainya selesai.',
            ]);
        }

        $benar = $this->hitungBenar($soal, $terjawab);
        $persen = (int) round($benar / $soal->count() * 100);
        $minimal = (int) config('gamifikasi.pemahaman.minimal_benar_persen');

        if ($persen < $minimal) {
            throw ValidationException::withMessages([
                'jawaban' => "Jawaban benar {$benar} dari {$soal->count()}. Baca ulang materinya, lalu coba lagi.",
            ]);
        }

        return ['total' => $soal->count(), 'benar' => $benar];
    }

    private function hitungBenar(Collection $soal, Collection $terjawab): int
    {
        return $soal->filter(function ($item) use ($terjawab) {
            return (int) $terjawab->get($item->id)['jawaban'] === $item->jawaban_benar;
        })->count();
    }
}
