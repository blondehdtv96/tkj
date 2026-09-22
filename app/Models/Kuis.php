<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kuis extends Model
{
    use HasFactory;

    protected $table = 'kuis';

    protected $fillable = [
        'bab_id',
        'judul',
        'durasi_menit',
        'kkm',
        'acak_soal',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'acak_soal' => 'boolean',
            'aktif' => 'boolean',
        ];
    }

    public function bab(): BelongsTo
    {
        return $this->belongsTo(Bab::class);
    }

    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class);
    }

    public function hasilKuis(): HasMany
    {
        return $this->hasMany(HasilKuis::class);
    }

    /**
     * Jumlah materi pada bab ini yang belum diselesaikan siswa. Kuis baru boleh
     * dikerjakan setelah seluruh materi pada babnya tuntas (nilai 0).
     */
    public function materiBelumSelesai(User $user): int
    {
        $materiIds = $this->bab?->materi->pluck('id') ?? collect();

        if ($materiIds->isEmpty()) {
            return 0;
        }

        $jumlahSelesai = ProgresMateri::where('user_id', $user->id)
            ->selesai()
            ->whereIn('materi_id', $materiIds)
            ->count();

        return max(0, $materiIds->count() - $jumlahSelesai);
    }
}
