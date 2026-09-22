<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JawabanSiswaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'soal' => new SoalResource($this->whenLoaded('soal')),
            'opsi_id' => $this->opsi_id,
            'jawaban_teks' => $this->jawaban_teks,
            'jawaban_urutan' => $this->jawaban_urutan,
            'is_benar' => $this->is_benar,
            'porsi_benar' => $this->porsi_benar === null ? null : (float) $this->porsi_benar,
        ];
    }
}
