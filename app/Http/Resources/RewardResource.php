<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RewardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $poinSiswa = $request->user()?->role === 'siswa' ? (int) $request->user()->total_poin : null;

        return [
            'id' => $this->id,
            'nama_barang' => $this->nama_barang,
            'deskripsi' => $this->deskripsi,
            'harga_poin' => $this->harga_poin,
            'stok' => $this->stok,
            'gambar_url' => $this->gambar ? Storage::url($this->gambar) : null,
            'aktif' => $this->aktif,
            'tersedia' => $this->tersedia(),
            'poin_kurang' => $poinSiswa === null ? null : max(0, $this->harga_poin - $poinSiswa),
            'created_at' => $this->created_at,
        ];
    }
}
