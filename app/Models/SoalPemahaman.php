<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoalPemahaman extends Model
{
    use HasFactory;

    protected $table = 'soal_pemahaman';

    protected $fillable = [
        'materi_id',
        'pertanyaan',
        'opsi',
        'jawaban_benar',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'opsi' => 'array',
            'jawaban_benar' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class);
    }
}
