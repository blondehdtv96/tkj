<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointsLogResource extends JsonResource
{
    private const LABEL_SUMBER = [
        'materi' => 'Materi Selesai',
        'kuis' => 'Kuis',
        'bonus_kkm' => 'Bonus Lulus KKM',
        'bonus_tepat_waktu' => 'Bonus Tepat Waktu',
        'bonus_bab' => 'Bonus Bab Tuntas',
        'streak' => 'Bonus Streak',
        'redeem' => 'Penukaran Reward',
        'refund' => 'Pengembalian Poin',
        'penyesuaian' => 'Penyesuaian',
    ];

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sumber' => $this->sumber,
            'sumber_label' => self::LABEL_SUMBER[$this->sumber] ?? $this->sumber,
            'jumlah' => $this->jumlah,
            'keterangan' => $this->keterangan,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}
