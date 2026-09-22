<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MataPelajaranRequest;
use App\Http\Resources\MataPelajaranResource;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $query = MataPelajaran::withCount('bab');

        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->integer('tingkat'));
        }

        return MataPelajaranResource::collection($query->orderBy('tingkat')->get());
    }

    public function show(MataPelajaran $mataPelajaran)
    {
        return new MataPelajaranResource($mataPelajaran->load(['bab' => fn ($q) => $q->withCount('materi')]));
    }

    public function store(MataPelajaranRequest $request)
    {
        $mataPelajaran = MataPelajaran::create($request->validated());

        return (new MataPelajaranResource($mataPelajaran))->response()->setStatusCode(201);
    }

    public function update(MataPelajaranRequest $request, MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->update($request->validated());

        return new MataPelajaranResource($mataPelajaran);
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->delete();

        return response()->json(['message' => 'Mata pelajaran dihapus.']);
    }
}
