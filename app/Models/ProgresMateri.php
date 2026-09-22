<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ProgresMateri extends Model
{
    use HasFactory;

    protected $table = 'progres_materi';

    protected $fillable = [
        'user_id',
        'materi_id',
        'status',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'selesai_pada' => 'datetime',
        ];
    }

    /**
     * Materi yang benar-benar tuntas. Baris berstatus "dibaca" hanya menandai
     * materi pernah dibuka dan tidak boleh ikut membuka kunci kuis.
     */
    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status', 'selesai');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class);
    }

    /**
     * Menandai atribut "selesai" pada tiap materi berdasarkan progres siswa.
     * Role selain siswa tidak memiliki progres sehingga atribut tidak dipasang.
     */
    public static function tandaiStatusSelesai(?User $user, Collection $materiList): void
    {
        if (! $user || $user->role !== 'siswa' || $materiList->isEmpty()) {
            return;
        }

        $selesaiIds = static::where('user_id', $user->id)
            ->selesai()
            ->whereIn('materi_id', $materiList->pluck('id'))
            ->pluck('materi_id')
            ->flip();

        $materiList->each(function (Materi $materi) use ($selesaiIds) {
            $materi->selesai = $selesaiIds->has($materi->id);
        });
    }
}
