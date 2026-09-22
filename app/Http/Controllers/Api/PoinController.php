<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenyesuaianPoinRequest;
use App\Http\Resources\PointsLogResource;
use App\Models\PointsLog;
use App\Models\User;
use App\Services\Gamifikasi\LeaderboardService;
use App\Services\Gamifikasi\LencanaService;
use App\Services\Gamifikasi\LevelService;
use App\Services\Gamifikasi\PoinService;
use Illuminate\Http\Request;

class PoinController extends Controller
{
    public function __construct(
        private PoinService $poinService,
        private LevelService $levelService,
        private LencanaService $lencanaService,
        private LeaderboardService $leaderboardService,
    ) {
    }

    /**
     * Ringkasan gamifikasi untuk dashboard poin siswa. Guru/admin dapat
     * melihat ringkasan siswa lain dengan query ?user_id=.
     */
    public function ringkasan(Request $request)
    {
        $user = $this->targetUser($request);

        $peringkatKelas = $this->leaderboardService->peringkat('bulanan', 'kelas', $user);

        return response()->json([
            'data' => [
                'user_id' => $user->id,
                'nama' => $user->name,
                'total_poin' => (int) $user->total_poin,
                'poin_seumur_hidup' => (int) PointsLog::where('user_id', $user->id)->perolehan()->sum('jumlah'),
                'streak_hari' => (int) $user->streak_hari,
                'streak_terakhir' => $user->streak_terakhir?->toDateString(),
                'level' => $this->levelService->progres((int) $user->total_poin),
                'lencana' => $this->lencanaService->untuk($user),
                'metrik' => $this->lencanaService->metrik($user),
                'peringkat_kelas' => $this->leaderboardService->posisi($peringkatKelas, $user->id),
                'poin_tertahan' => (int) $user->redemptions()->whereIn('status', ['pending', 'approved'])->sum('jumlah_poin'),
            ],
        ]);
    }

    /**
     * Riwayat mutasi poin. Siswa hanya melihat miliknya sendiri.
     */
    public function riwayat(Request $request)
    {
        $query = PointsLog::with('user')->latest();

        if ($request->user()->role === 'siswa') {
            $query->where('user_id', $request->user()->id);
        } else {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->integer('user_id'));
            }

            if ($request->filled('kelas_id')) {
                $query->whereHas('user', fn ($q) => $q->where('kelas_id', $request->integer('kelas_id')));
            }
        }

        if ($request->filled('sumber')) {
            $query->where('sumber', $request->get('sumber'));
        }

        return PointsLogResource::collection($query->paginate(20));
    }

    /**
     * Penyesuaian poin manual oleh guru/admin.
     */
    public function penyesuaian(PenyesuaianPoinRequest $request)
    {
        $siswa = User::findOrFail($request->validated('user_id'));

        $log = $this->poinService->penyesuaian(
            $siswa,
            (int) $request->validated('jumlah'),
            $request->validated('keterangan'),
        );

        return (new PointsLogResource($log->load('user')))->response()->setStatusCode(201);
    }

    private function targetUser(Request $request): User
    {
        if ($request->user()->role !== 'siswa' && $request->filled('user_id')) {
            return User::with('kelas')->findOrFail($request->integer('user_id'));
        }

        return $request->user()->loadMissing('kelas');
    }
}
