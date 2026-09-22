<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HasilKuisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'kuis' => new KuisResource($this->whenLoaded('kuis')),
            'skor' => $this->skor === null ? null : (float) $this->skor,
            'waktu_mulai' => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            'selesai' => $this->waktu_selesai !== null,
            'jawaban' => JawabanSiswaResource::collection($this->whenLoaded('jawabanSiswa')),
        ];
    }
}
