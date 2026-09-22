<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferensiBelajarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bab_id' => $this->bab_id,
            'ringkasan' => $this->ringkasan,
            'video_judul' => $this->video_judul,
            'video_url' => $this->video_url,
            'kasus_soal' => $this->kasus_soal,
        ];
    }
}
