<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MateriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bab_id' => $this->bab_id,
            'judul' => $this->judul,
            'konten_html' => $this->konten_html,
            'tipe' => $this->tipe,
            'file_url' => $this->file_path ? Storage::url($this->file_path) : null,
            'urutan' => $this->urutan,
            'selesai' => $this->when(isset($this->selesai), fn () => (bool) $this->selesai),
            'jumlah_soal_pemahaman' => $this->whenCounted('soalPemahaman'),
        ];
    }
}
