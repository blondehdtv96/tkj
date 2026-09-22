<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferensiBelajar extends Model
{
    use HasFactory;

    protected $table = 'referensi_belajar';

    protected $fillable = [
        'bab_id',
        'ringkasan',
        'video_judul',
        'video_url',
        'kasus_soal',
    ];

    public function bab(): BelongsTo
    {
        return $this->belongsTo(Bab::class);
    }
}
