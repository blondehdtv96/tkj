<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Gamifikasi\PoinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private PoinService $poinService)
    {
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Siswa masuk dengan NIS, guru dan admin masuk dengan username.
        $user = User::where('username', $data['identifier'])
            ->orWhere('nis', $data['identifier'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => 'NIS/username atau password salah.',
            ]);
        }

        $token = $user->createToken('spa-token')->plainTextToken;

        // Streak harian dicatat saat login sehingga siswa yang belajar tiap
        // hari mendapat bonus tanpa aksi tambahan.
        $bonusStreak = $user->isSiswa() ? $this->poinService->catatStreak($user) : null;

        return response()->json([
            'user' => new UserResource($user->fresh()->load('kelas')),
            'token' => $token,
            'bonus_streak' => $bonusStreak ? [
                'jumlah' => $bonusStreak->jumlah,
                'keterangan' => $bonusStreak->keterangan,
                'streak_hari' => (int) $user->streak_hari,
            ] : null,
        ]);
    }

    public function user(Request $request)
    {
        return new UserResource($request->user()->load('kelas'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }
}
