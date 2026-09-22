<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    use HasFactory;

    protected $table = 'rewards';

    protected $fillable = [
        'nama_barang',
        'deskripsi',
        'harga_poin',
        'stok',
        'gambar',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga_poin' => 'integer',
            'stok' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function tersedia(): bool
    {
        return $this->aktif && $this->stok > 0;
    }
}
