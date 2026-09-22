<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpsiJawabanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'soal_id' => $this->soal_id,
            'teks' => $this->teks,
            'is_benar' => (bool) $this->is_benar,
            'urutan' => $this->urutan,
        ];
    }
}
