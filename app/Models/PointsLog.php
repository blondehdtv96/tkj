<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsLog extends Model
{
    use HasFactory;

    protected $table = 'points_log';

    protected $fillable = [
        'user_id',
        'sumber',
        'jumlah',
        'keterangan',
        'kunci_unik',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hanya perolehan poin — penukaran reward dan penyesuaian negatif
     * dikecualikan agar leaderboard mencerminkan hasil belajar.
     */
    public function scopePerolehan(Builder $query): Builder
    {
        return $query->where('jumlah', '>', 0)->whereNotIn('sumber', ['refund']);
    }
}
