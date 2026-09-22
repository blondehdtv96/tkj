<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PoinKontenRequest;
use App\Models\Bab;
use App\Models\Materi;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pengaturan poin gamifikasi untuk seluruh konten satu bab dalam satu layar:
 * poin tiap materi dan poin tiap soal pada kuis bab tersebut.
 */
class PoinKontenController extends Controller
{
    public function index(Request $request)
    {
        $bab = Bab::with([
            'materi' => fn ($q) => $q->orderBy('urutan'),
            'kuis.soal' => fn ($q) => $q->orderBy('id'),
        ])->findOrFail($request->integer('bab_id'));

        return response()->json([
            'data' => [
                'bab' => ['id' => $bab->id, 'judul' => $bab->judul],
                'bawaan' => [
                    'materi' => (int) config('gamifikasi.poin.materi_selesai'),
                    'soal' => (int) config('gamifikasi.poin.soal_default'),
                    'kuis_maksimal' => (int) config('gamifikasi.poin.kuis_maksimal'),
                ],
                'materi' => $bab->materi->map(fn (Materi $materi) => [
                    'id' => $materi->id,
                    'judul' => $materi->judul,
                    'tipe' => $materi->tipe,
                    'poin' => $materi->poin,
                    'poin_efektif' => $materi->poinGamifikasi(),
                ])->values(),
                'kuis' => $bab->kuis->map(fn ($kuis) => [
                    'id' => $kuis->id,
                    'judul' => $kuis->judul,
                    'total_poin' => $kuis->soal->sum(fn (Soal $soal) => $soal->poinGamifikasi()),
                    'soal' => $kuis->soal->map(fn (Soal $soal) => [
                        'id' => $soal->id,
                        'pertanyaan' => $soal->pertanyaan,
                        'tipe' => $soal->tipe,
                        'bobot' => $soal->bobot,
                        'poin' => $soal->poin,
                        'poin_efektif' => $soal->poinGamifikasi(),
                    ])->values(),
                ])->values(),
            ],
        ]);
    }

    public function update(PoinKontenRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            foreach ($data['materi'] ?? [] as $baris) {
                Materi::whereKey($baris['id'])->update(['poin' => $baris['poin'] ?? null]);
            }

            foreach ($data['soal'] ?? [] as $baris) {
                Soal::whereKey($baris['id'])->update(['poin' => $baris['poin'] ?? null]);
            }
        });

        return response()->json([
            'message' => 'Pengaturan poin disimpan.',
            'jumlah_materi' => count($data['materi'] ?? []),
            'jumlah_soal' => count($data['soal'] ?? []),
        ]);
    }
}
