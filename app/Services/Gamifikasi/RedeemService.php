<?php

namespace App\Services\Gamifikasi;

use App\Events\RedemptionApproved;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Alur penukaran reward: poin langsung ditahan saat pengajuan (bukan saat
 * disetujui) agar siswa tidak dapat membelanjakan poin yang sama dua kali
 * sambil menunggu verifikasi. Poin dikembalikan hanya bila pengajuan ditolak.
 */
class RedeemService
{
    public function __construct(private PoinService $poinService)
    {
    }

    public function ajukan(User $user, Reward $reward, ?string $catatan = null): Redemption
    {
        $redemption = DB::transaction(function () use ($user, $reward, $catatan) {
            $reward = Reward::whereKey($reward->id)->lockForUpdate()->first();
            $user = User::whereKey($user->id)->lockForUpdate()->first();

            if (! $reward->aktif) {
                throw ValidationException::withMessages(['reward_id' => 'Reward ini sedang tidak tersedia.']);
            }

            if ($reward->stok < 1) {
                throw ValidationException::withMessages(['reward_id' => 'Stok reward sudah habis.']);
            }

            if ($user->total_poin < $reward->harga_poin) {
                $kurang = $reward->harga_poin - $user->total_poin;

                throw ValidationException::withMessages([
                    'reward_id' => "Poin belum mencukupi. Kurang {$kurang} poin lagi.",
                ]);
            }

            $reward->decrement('stok');

            $redemption = Redemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'jumlah_poin' => $reward->harga_poin,
                'status' => 'pending',
                'catatan_siswa' => $catatan,
            ]);

            $this->poinService->catat(
                $user,
                'redeem',
                -$reward->harga_poin,
                "Penukaran reward: {$reward->nama_barang}",
                "redeem:{$redemption->id}",
            );

            return $redemption;
        });

        return $redemption->load(['reward', 'user']);
    }

    public function setujui(Redemption $redemption, User $petugas, ?string $catatan = null): Redemption
    {
        $this->pastikanStatus($redemption, ['pending']);

        $redemption->update([
            'status' => 'approved',
            'catatan_petugas' => $catatan,
            'diproses_oleh' => $petugas->id,
            'diproses_pada' => now(),
        ]);

        RedemptionApproved::dispatch($redemption);

        return $redemption->load(['reward', 'user', 'petugas']);
    }

    public function selesaikan(Redemption $redemption, User $petugas, ?string $catatan = null): Redemption
    {
        $this->pastikanStatus($redemption, ['pending', 'approved']);

        $redemption->update([
            'status' => 'completed',
            'catatan_petugas' => $catatan ?? $redemption->catatan_petugas,
            'diproses_oleh' => $petugas->id,
            'diproses_pada' => now(),
        ]);

        return $redemption->load(['reward', 'user', 'petugas']);
    }

    /**
     * Menolak pengajuan: poin dikembalikan dan stok reward dipulihkan.
     */
    public function tolak(Redemption $redemption, User $petugas, ?string $catatan = null): Redemption
    {
        $this->pastikanStatus($redemption, ['pending', 'approved']);

        DB::transaction(function () use ($redemption, $petugas, $catatan) {
            $redemption->update([
                'status' => 'rejected',
                'catatan_petugas' => $catatan,
                'diproses_oleh' => $petugas->id,
                'diproses_pada' => now(),
            ]);

            $redemption->reward()->increment('stok');

            $this->poinService->catat(
                $redemption->user,
                'refund',
                $redemption->jumlah_poin,
                "Pengembalian poin, penukaran ditolak: {$redemption->reward->nama_barang}",
                "refund:{$redemption->id}",
            );
        });

        return $redemption->fresh(['reward', 'user', 'petugas']);
    }

    /**
     * @param  array<int, string>  $diizinkan
     */
    private function pastikanStatus(Redemption $redemption, array $diizinkan): void
    {
        if (! in_array($redemption->status, $diizinkan, true)) {
            throw ValidationException::withMessages([
                'status' => "Pengajuan berstatus {$redemption->status} tidak dapat diproses lagi.",
            ]);
        }
    }
}
