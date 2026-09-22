<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KuisRequest;
use App\Http\Resources\KuisResource;
use App\Models\Kuis;
use Illuminate\Http\Request;

class KuisController extends Controller
{
    public function index(Request $request)
    {
        $query = Kuis::withCount('soal')->with('bab.materi');

        if ($request->filled('bab_id')) {
            $query->where('bab_id', $request->integer('bab_id'));
        }

        if ($request->user()->role === 'siswa') {
            $query->where('aktif', true);
        }

        return KuisResource::collection($query->get());
    }

    public function show(Kuis $kuis)
    {
        return new KuisResource($kuis->load(['bab', 'soal.opsiJawaban']));
    }

    public function store(KuisRequest $request)
    {
        $kuis = Kuis::create($request->validated());

        return (new KuisResource($kuis))->response()->setStatusCode(201);
    }

    public function update(KuisRequest $request, Kuis $kuis)
    {
        $kuis->update($request->validated());

        return new KuisResource($kuis);
    }

    public function destroy(Kuis $kuis)
    {
        $kuis->delete();

        return response()->json(['message' => 'Kuis dihapus.']);
    }
}
