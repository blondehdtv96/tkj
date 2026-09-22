<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kuis_id' => $this->kuis_id,
            'pertanyaan' => $this->pertanyaan,
            'skenario' => $this->skenario,
            'tipe' => $this->tipe,
            'gambar_url' => $this->gambar ? Storage::url($this->gambar) : null,
            'pembahasan' => $this->pembahasan,
            'bobot' => $this->bobot,
            'poin' => $this->poin,
            'poin_efektif' => $this->poinGamifikasi(),
            'opsi_jawaban' => OpsiJawabanResource::collection($this->whenLoaded('opsiJawaban')),
        ];
    }
}
