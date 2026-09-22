<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materi';

    protected $fillable = [
        'bab_id',
        'judul',
        'konten_html',
        'tipe',
        'file_path',
        'urutan',
        'poin',
    ];

    protected function casts(): array
    {
        return [
            'poin' => 'integer',
        ];
    }

    /**
     * Poin gamifikasi materi ini, dengan nilai bawaan bila admin belum mengaturnya.
     */
    public function poinGamifikasi(): int
    {
        return $this->poin ?? (int) config('gamifikasi.poin.materi_selesai');
    }

    public function bab(): BelongsTo
    {
        return $this->belongsTo(Bab::class);
    }

    public function progres(): HasMany
    {
        return $this->hasMany(ProgresMateri::class);
    }

    public function soalPemahaman(): HasMany
    {
        return $this->hasMany(SoalPemahaman::class)->orderBy('urutan');
    }
}
