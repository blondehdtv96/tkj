<?php

namespace App\Events;

use App\Models\Redemption;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dipancarkan ketika guru/admin menyetujui pengajuan penukaran reward.
 */
class RedemptionApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public Redemption $redemption)
    {
    }
}
