<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Soal extends Model
{
    use HasFactory;

    protected $table = 'soal';

    protected $fillable = [
        'kuis_id',
        'pertanyaan',
        'skenario',
        'tipe',
        'gambar',
        'pembahasan',
        'bobot',
        'poin',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'integer',
            'poin' => 'integer',
        ];
    }

    public function kuis(): BelongsTo
    {
        return $this->belongsTo(Kuis::class);
    }

    public function opsiJawaban(): HasMany
    {
        return $this->hasMany(OpsiJawaban::class);
    }

    /**
     * Langkah penanganan soal troubleshooting, terurut sesuai kunci jawaban.
     */
    public function langkah(): HasMany
    {
        return $this->hasMany(OpsiJawaban::class)->orderBy('urutan');
    }

    public function isTroubleshooting(): bool
    {
        return $this->tipe === 'troubleshooting';
    }

    /**
     * Poin gamifikasi soal ini, dengan nilai bawaan bila admin belum mengaturnya.
     */
    public function poinGamifikasi(): int
    {
        return $this->poin ?? (int) config('gamifikasi.poin.soal_default');
    }

    public function jawabanSiswa(): HasMany
    {
        return $this->hasMany(JawabanSiswa::class);
    }
}
