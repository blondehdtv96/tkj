<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Kunci jawaban hanya disertakan untuk guru dan admin. Siswa yang sedang
 * mengerjakan kuis pemahaman menerima pertanyaan dan opsi saja.
 */
class SoalPemahamanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $bolehLihatKunci = in_array($request->user()?->role, ['guru', 'admin'], true);

        return [
            'id' => $this->id,
            'materi_id' => $this->materi_id,
            'pertanyaan' => $this->pertanyaan,
            'opsi' => $this->opsi,
            'urutan' => $this->urutan,
            'jawaban_benar' => $this->when($bolehLihatKunci, fn () => $this->jawaban_benar),
        ];
    }
}
