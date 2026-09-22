<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BabResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mata_pelajaran_id' => $this->mata_pelajaran_id,
            'judul' => $this->judul,
            'urutan' => $this->urutan,
            'materi' => MateriResource::collection($this->whenLoaded('materi')),
            'kuis' => KuisResource::collection($this->whenLoaded('kuis')),
            'jumlah_materi' => $this->whenCounted('materi'),
            'referensi_belajar' => $this->whenLoaded(
                'referensiBelajar',
                fn () => $this->referensiBelajar ? new ReferensiBelajarResource($this->referensiBelajar) : null
            ),
        ];
    }
}
