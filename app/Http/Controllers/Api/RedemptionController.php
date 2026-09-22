<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RedemptionRequest;
use App\Http\Requests\RedemptionStatusRequest;
use App\Http\Resources\RedemptionResource;
use App\Models\Redemption;
use App\Models\Reward;
use App\Services\Gamifikasi\RedeemService;
use Illuminate\Http\Request;

class RedemptionController extends Controller
{
    public function __construct(private RedeemService $redeemService)
    {
    }

    public function index(Request $request)
    {
        $query = Redemption::with(['reward', 'user.kelas', 'petugas'])->latest();

        if ($request->user()->role === 'siswa') {
            $query->where('user_id', $request->user()->id);
        } else {
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('kelas_id')) {
                $query->whereHas('user', fn ($q) => $q->where('kelas_id', $request->integer('kelas_id')));
            }
        }

        return RedemptionResource::collection($query->paginate(20));
    }

    public function show(Request $request, Redemption $redemption)
    {
        if ($request->user()->role === 'siswa' && $redemption->user_id !== $request->user()->id) {
            abort(403);
        }

        return new RedemptionResource($redemption->load(['reward', 'user.kelas', 'petugas']));
    }

    /**
     * Pengajuan penukaran oleh siswa. Poin langsung ditahan di sini.
     */
    public function store(RedemptionRequest $request)
    {
        $reward = Reward::findOrFail($request->validated('reward_id'));

        $redemption = $this->redeemService->ajukan(
            $request->user(),
            $reward,
            $request->validated('catatan_siswa'),
        );

        return (new RedemptionResource($redemption->load(['reward', 'user.kelas'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Verifikasi oleh guru/admin: setujui, tandai sudah diambil, atau tolak.
     */
    public function ubahStatus(RedemptionStatusRequest $request, Redemption $redemption)
    {
        $petugas = $request->user();
        $catatan = $request->validated('catatan_petugas');

        $redemption = match ($request->validated('status')) {
            'approved' => $this->redeemService->setujui($redemption, $petugas, $catatan),
            'completed' => $this->redeemService->selesaikan($redemption, $petugas, $catatan),
            'rejected' => $this->redeemService->tolak($redemption, $petugas, $catatan),
        };

        return new RedemptionResource($redemption->load(['reward', 'user.kelas', 'petugas']));
    }
}
