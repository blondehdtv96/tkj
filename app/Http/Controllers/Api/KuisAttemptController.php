<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HasilKuisResource;
use App\Http\Resources\SoalAttemptResource;
use App\Models\HasilKuis;
use App\Models\JawabanSiswa;
use App\Models\Kuis;
use App\Models\Soal;
use App\Services\Gamifikasi\PoinService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KuisAttemptController extends Controller
{
    public function __construct(private PoinService $poinService)
    {
    }

    /**
     * Mulai (atau lanjutkan) pengerjaan kuis. Soal diacak dan waktu mulai
     * dicatat di server. Kunci jawaban tidak pernah dikirim di sini.
     */
    public function mulai(Request $request, Kuis $kuis)
    {
        if (! $kuis->aktif) {
            throw ValidationException::withMessages(['kuis' => 'Kuis ini tidak aktif.']);
        }

        $user = $request->user();

        $belumSelesai = $kuis->loadMissing('bab.materi')->materiBelumSelesai($user);

        if ($belumSelesai > 0) {
            throw ValidationException::withMessages([
                'kuis' => "Selesaikan dulu {$belumSelesai} materi pada bab ini sebelum mengerjakan kuis.",
            ]);
        }

        $hasilKuis = HasilKuis::where('user_id', $user->id)
            ->where('kuis_id', $kuis->id)
            ->whereNull('waktu_selesai')
            ->first();

        if (! $hasilKuis) {
            $hasilKuis = HasilKuis::create([
                'user_id' => $user->id,
                'kuis_id' => $kuis->id,
                'waktu_mulai' => now(),
            ]);
        }

        $soal = $kuis->soal()->with('opsiJawaban')->get();

        if ($kuis->acak_soal) {
            $soal = $soal->shuffle()->values();
        }

        return response()->json([
            'data' => [
                'hasil_kuis_id' => $hasilKuis->id,
                'kuis' => [
                    'id' => $kuis->id,
                    'judul' => $kuis->judul,
                    'durasi_menit' => $kuis->durasi_menit,
                ],
                'waktu_mulai' => $hasilKuis->waktu_mulai,
                'soal' => SoalAttemptResource::collection($soal),
            ],
        ]);
    }

    /**
     * Submit jawaban dan koreksi otomatis di server. Idempoten: submit
     * ulang pada hasil kuis yang sudah selesai hanya mengembalikan hasilnya.
     */
    public function submit(Request $request, HasilKuis $hasilKuis)
    {
        if ($hasilKuis->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($hasilKuis->waktu_selesai !== null) {
            return new HasilKuisResource($hasilKuis->load(['jawabanSiswa.soal.opsiJawaban', 'kuis']));
        }

        $data = $request->validate([
            'jawaban' => ['required', 'array'],
            'jawaban.*.soal_id' => ['required', 'exists:soal,id'],
            'jawaban.*.opsi_id' => ['nullable', 'exists:opsi_jawaban,id'],
            'jawaban.*.jawaban_teks' => ['nullable', 'string'],
            'jawaban.*.jawaban_urutan' => ['nullable', 'array'],
            'jawaban.*.jawaban_urutan.*' => ['integer'],
        ]);

        DB::transaction(function () use ($hasilKuis, $data) {
            $kuis = $hasilKuis->kuis()->with('soal.opsiJawaban')->first();
            $skorAutoTotal = 0;
            $bobotAutoTotal = 0;

            foreach ($kuis->soal as $soal) {
                $jawaban = collect($data['jawaban'])->firstWhere('soal_id', $soal->id);
                $opsiId = $jawaban['opsi_id'] ?? null;
                $jawabanTeks = $jawaban['jawaban_teks'] ?? null;
                $jawabanUrutan = $jawaban['jawaban_urutan'] ?? null;

                $isBenar = null;
                $porsiBenar = null;

                if ($soal->tipe === 'troubleshooting') {
                    $porsiBenar = $this->porsiUrutanBenar($soal, $jawabanUrutan);
                    $isBenar = $porsiBenar >= 1.0;
                } elseif ($soal->tipe !== 'essay') {
                    $opsiBenar = $soal->opsiJawaban->firstWhere('is_benar', true);
                    $isBenar = $opsiId !== null && $opsiBenar && (int) $opsiId === $opsiBenar->id;
                    $porsiBenar = $isBenar ? 1.0 : 0.0;
                }

                // Essay tidak dinilai server, jadi bobotnya tidak ikut membagi skor.
                if ($soal->tipe !== 'essay') {
                    $bobotAutoTotal += $soal->bobot;
                    $skorAutoTotal += $soal->bobot * $porsiBenar;
                }

                JawabanSiswa::updateOrCreate(
                    ['hasil_kuis_id' => $hasilKuis->id, 'soal_id' => $soal->id],
                    [
                        'opsi_id' => $opsiId,
                        'jawaban_teks' => $jawabanTeks,
                        'jawaban_urutan' => $jawabanUrutan,
                        'is_benar' => $isBenar,
                        'porsi_benar' => $porsiBenar,
                    ]
                );
            }

            $skor = $bobotAutoTotal > 0 ? round(($skorAutoTotal / $bobotAutoTotal) * 100, 2) : null;

            $hasilKuis->update([
                'skor' => $skor,
                'waktu_selesai' => now(),
            ]);
        });

        $hasilKuis = $hasilKuis->fresh(['jawabanSiswa.soal.opsiJawaban', 'kuis']);

        // Poin baru dihitung setelah skor tersimpan; kunci idempotensi di
        // PoinService memastikan submit ulang tidak menggandakan poin.
        $poin = $this->poinService->dariKuis($request->user(), $hasilKuis);

        return $this->responsHasil($request, $hasilKuis, $poin);
    }

    /**
     * Porsi langkah troubleshooting yang ditempatkan siswa pada posisi benar,
     * bernilai 0..1. Dipakai untuk skor maupun poin sebagian.
     *
     * @param  array<int, int>|null  $jawabanUrutan
     */
    private function porsiUrutanBenar(Soal $soal, ?array $jawabanUrutan): float
    {
        $kunci = $soal->opsiJawaban->sortBy('urutan')->pluck('id')->values();

        if ($kunci->isEmpty() || empty($jawabanUrutan)) {
            return 0.0;
        }

        $tepat = $kunci->filter(
            fn ($opsiId, $posisi) => isset($jawabanUrutan[$posisi]) && (int) $jawabanUrutan[$posisi] === $opsiId
        )->count();

        return round($tepat / $kunci->count(), 4);
    }

    /**
     * @param  Collection<int, \App\Models\PointsLog>  $poin
     */
    private function responsHasil(Request $request, HasilKuis $hasilKuis, Collection $poin)
    {
        return (new HasilKuisResource($hasilKuis))->additional([
            'gamifikasi' => [
                'poin_diperoleh' => $poin->sum('jumlah'),
                'rincian' => $poin->map(fn ($log) => [
                    'sumber' => $log->sumber,
                    'jumlah' => $log->jumlah,
                    'keterangan' => $log->keterangan,
                ])->values(),
                'total_poin' => (int) $request->user()->fresh()->total_poin,
            ],
        ]);
    }
}
