<?php

namespace App\Events;

use App\Models\PointsLog;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dipancarkan setiap kali poin benar-benar masuk ke saldo siswa.
 * Titik sambung untuk notifikasi tanpa menambah beban di service poin.
 */
class PointsEarned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public PointsLog $log,
        public bool $levelNaik = false,
    ) {
    }
}
