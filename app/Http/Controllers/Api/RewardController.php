<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RewardRequest;
use App\Http\Resources\RewardResource;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        $query = Reward::query();

        // Siswa hanya melihat katalog yang masih dibuka sekolah.
        if ($request->user()->role === 'siswa') {
            $query->where('aktif', true);
        } elseif ($request->filled('aktif')) {
            $query->where('aktif', $request->boolean('aktif'));
        }

        if ($request->filled('search')) {
            $query->where('nama_barang', 'like', '%'.$request->get('search').'%');
        }

        return RewardResource::collection($query->orderBy('harga_poin')->get());
    }

    public function store(RewardRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('rewards', 'public');
        }

        $reward = Reward::create($data);

        return (new RewardResource($reward))->response()->setStatusCode(201);
    }

    public function update(RewardRequest $request, Reward $reward)
    {
        $data = $request->validated();

        if ($request->hasFile('gambar')) {
            if ($reward->gambar) {
                Storage::disk('public')->delete($reward->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('rewards', 'public');
        }

        $reward->update($data);

        return new RewardResource($reward);
    }

    public function destroy(Reward $reward)
    {
        // Riwayat penukaran harus tetap terbaca, jadi reward yang sudah pernah
        // ditukar hanya dinonaktifkan, bukan dihapus.
        if ($reward->redemptions()->exists()) {
            if (! $reward->aktif) {
                throw ValidationException::withMessages([
                    'reward' => 'Reward ini sudah pernah ditukar sehingga tidak dapat dihapus.',
                ]);
            }

            $reward->update(['aktif' => false]);

            return response()->json(['message' => 'Reward sudah pernah ditukar, jadi dinonaktifkan dari katalog.']);
        }

        if ($reward->gambar) {
            Storage::disk('public')->delete($reward->gambar);
        }

        $reward->delete();

        return response()->json(['message' => 'Reward dihapus.']);
    }
}
