<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends Model
{
    use HasFactory;

    protected $table = 'redemptions';

    protected $fillable = [
        'user_id',
        'reward_id',
        'jumlah_poin',
        'status',
        'catatan_siswa',
        'catatan_petugas',
        'diproses_oleh',
        'diproses_pada',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_poin' => 'integer',
            'diproses_pada' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    /**
     * Poin masih tertahan selama pengajuan belum ditolak, sehingga siswa
     * tidak bisa membelanjakan poin yang sama dua kali.
     */
    public function poinTertahan(): bool
    {
        return $this->status !== 'rejected';
    }
}
