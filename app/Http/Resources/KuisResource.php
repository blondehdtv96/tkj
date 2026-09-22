<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KuisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isSiswa = $user?->role === 'siswa';
        $belumSelesai = $isSiswa ? $this->materiBelumSelesai($user) : 0;

        return [
            'id' => $this->id,
            'bab_id' => $this->bab_id,
            'bab_judul' => $this->whenLoaded('bab', fn () => $this->bab->judul),
            'judul' => $this->judul,
            'durasi_menit' => $this->durasi_menit,
            'kkm' => $this->kkm,
            'acak_soal' => (bool) $this->acak_soal,
            'aktif' => (bool) $this->aktif,
            'jumlah_soal' => $this->whenCounted('soal'),
            'soal' => SoalResource::collection($this->whenLoaded('soal')),
            'terkunci' => $this->when($isSiswa, fn () => $belumSelesai > 0),
            'materi_belum_selesai' => $this->when($isSiswa, fn () => $belumSelesai),
        ];
    }
}
