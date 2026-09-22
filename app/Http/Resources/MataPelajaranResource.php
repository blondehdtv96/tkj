<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MataPelajaranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'tingkat' => $this->tingkat,
            'deskripsi' => $this->deskripsi,
            'bab' => BabResource::collection($this->whenLoaded('bab')),
            'jumlah_bab' => $this->whenCounted('bab'),
        ];
    }
}
