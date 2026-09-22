<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KelasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tingkat' => $this->tingkat,
            'nama_rombel' => $this->nama_rombel,
            'wali_kelas_id' => $this->wali_kelas_id,
            'wali_kelas' => new UserResource($this->whenLoaded('waliKelas')),
            'jumlah_siswa' => $this->whenCounted('siswa'),
        ];
    }
}
