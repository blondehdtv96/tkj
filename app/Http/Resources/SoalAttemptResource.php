<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Representasi soal untuk siswa yang sedang mengerjakan kuis.
 * Tidak pernah menyertakan opsi_jawaban.is_benar, urutan kunci, maupun pembahasan.
 */
class SoalAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pertanyaan' => $this->pertanyaan,
            'skenario' => $this->skenario,
            'tipe' => $this->tipe,
            'gambar_url' => $this->gambar ? Storage::url($this->gambar) : null,
            'bobot' => $this->bobot,
            'poin' => $this->poinGamifikasi(),
            'opsi_jawaban' => $this->opsiUntukSiswa(),
        ];
    }

    /**
     * Langkah soal troubleshooting selalu diacak: urutan penyimpanannya adalah
     * kunci jawaban, jadi mengirimnya apa adanya sama saja membocorkan kunci.
     */
    private function opsiUntukSiswa()
    {
        $opsi = $this->opsiJawaban;

        if ($this->tipe === 'troubleshooting') {
            $opsi = $opsi->shuffle();
        }

        return $opsi->map(fn ($item) => [
            'id' => $item->id,
            'teks' => $item->teks,
        ])->values();
    }
}
