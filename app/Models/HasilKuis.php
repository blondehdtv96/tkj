<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilKuis extends Model
{
    use HasFactory;

    protected $table = 'hasil_kuis';

    protected $fillable = [
        'user_id',
        'kuis_id',
        'skor',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kuis(): BelongsTo
    {
        return $this->belongsTo(Kuis::class);
    }

    public function jawabanSiswa(): HasMany
    {
        return $this->hasMany(JawabanSiswa::class);
    }
}
