<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bab extends Model
{
    use HasFactory;

    protected $table = 'bab';

    protected $fillable = [
        'mata_pelajaran_id',
        'judul',
        'urutan',
    ];

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function materi(): HasMany
    {
        return $this->hasMany(Materi::class)->orderBy('urutan');
    }

    public function kuis(): HasMany
    {
        return $this->hasMany(Kuis::class);
    }

    public function referensiBelajar(): HasOne
    {
        return $this->hasOne(ReferensiBelajar::class);
    }
}
