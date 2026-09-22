<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanSiswa extends Model
{
    use HasFactory;

    protected $table = 'jawaban_siswa';

    protected $fillable = [
        'hasil_kuis_id',
        'soal_id',
        'opsi_id',
        'jawaban_teks',
        'jawaban_urutan',
        'is_benar',
        'porsi_benar',
    ];

    protected function casts(): array
    {
        return [
            'is_benar' => 'boolean',
            'jawaban_urutan' => 'array',
            'porsi_benar' => 'float',
        ];
    }

    public function hasilKuis(): BelongsTo
    {
        return $this->belongsTo(HasilKuis::class);
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }

    public function opsi(): BelongsTo
    {
        return $this->belongsTo(OpsiJawaban::class, 'opsi_id');
    }
}
