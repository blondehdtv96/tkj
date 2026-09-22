<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RedemptionResource extends JsonResource
{
    private const LABEL_STATUS = [
        'pending' => 'Menunggu Verifikasi',
        'approved' => 'Disetujui',
        'completed' => 'Sudah Diambil',
        'rejected' => 'Ditolak',
    ];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'reward' => new RewardResource($this->whenLoaded('reward')),
            'jumlah_poin' => $this->jumlah_poin,
            'status' => $this->status,
            'status_label' => self::LABEL_STATUS[$this->status] ?? $this->status,
            'catatan_siswa' => $this->catatan_siswa,
            'catatan_petugas' => $this->catatan_petugas,
            'petugas' => new UserResource($this->whenLoaded('petugas')),
            'diproses_pada' => $this->diproses_pada,
            'created_at' => $this->created_at,
        ];
    }
}
