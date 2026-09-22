<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'nama',
        'tingkat',
        'deskripsi',
    ];

    public function bab(): HasMany
    {
        return $this->hasMany(Bab::class)->orderBy('urutan');
    }
}
