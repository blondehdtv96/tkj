<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KelasRequest;
use App\Http\Resources\KelasResource;
use App\Models\Kelas;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::withCount('siswa')->with('waliKelas')->orderBy('tingkat')->get();

        return KelasResource::collection($kelas);
    }

    public function store(KelasRequest $request)
    {
        $kelas = Kelas::create($request->validated());

        return (new KelasResource($kelas->load('waliKelas')))->response()->setStatusCode(201);
    }

    public function update(KelasRequest $request, Kelas $kelas)
    {
        $kelas->update($request->validated());

        return new KelasResource($kelas->load('waliKelas'));
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return response()->json(['message' => 'Kelas dihapus.']);
    }
}
