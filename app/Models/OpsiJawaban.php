<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsiJawaban extends Model
{
    use HasFactory;

    protected $table = 'opsi_jawaban';

    protected $fillable = [
        'soal_id',
        'teks',
        'is_benar',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_benar' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }
}
