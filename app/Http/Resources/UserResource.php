<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'nis' => $this->nis,
            'nip' => $this->nip,
            'username' => $this->username,
            'kelas_id' => $this->kelas_id,
            'kelas' => new KelasResource($this->whenLoaded('kelas')),
            'total_poin' => (int) $this->total_poin,
            'level' => $this->level,
            'streak_hari' => (int) $this->streak_hari,
            'created_at' => $this->created_at,
        ];
    }
}
