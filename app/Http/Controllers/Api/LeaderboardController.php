<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Gamifikasi\LeaderboardService;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(private LeaderboardService $leaderboardService)
    {
    }

    public function index(Request $request)
    {
        $periode = in_array($request->get('periode'), LeaderboardService::PERIODE, true)
            ? $request->get('periode')
            : 'mingguan';

        $lingkup = in_array($request->get('lingkup'), LeaderboardService::LINGKUP, true)
            ? $request->get('lingkup')
            : 'kelas';

        $user = $request->user()->loadMissing('kelas');

        $peringkat = $this->leaderboardService->peringkat(
            $periode,
            $lingkup,
            $user,
            $request->filled('kelas_id') ? $request->integer('kelas_id') : null,
            $request->filled('tingkat') ? $request->integer('tingkat') : null,
        );

        return response()->json([
            'data' => $peringkat,
            'meta' => [
                'periode' => $periode,
                'lingkup' => $lingkup,
                'mulai' => $this->leaderboardService->awalPeriode($periode)->toDateString(),
                'posisi_saya' => $this->leaderboardService->posisi($peringkat, $user->id),
            ],
        ]);
    }
}
