<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SoalPemahamanRequest;
use App\Http\Resources\SoalPemahamanResource;
use App\Models\SoalPemahaman;
use Illuminate\Http\Request;

class SoalPemahamanController extends Controller
{
    /**
     * Daftar soal pemahaman sebuah materi. Kunci jawaban hanya ikut untuk
     * guru dan admin — lihat SoalPemahamanResource.
     */
    public function index(Request $request)
    {
        $query = SoalPemahaman::query()->orderBy('urutan');

        if ($request->filled('materi_id')) {
            $query->where('materi_id', $request->integer('materi_id'));
        }

        return SoalPemahamanResource::collection($query->get());
    }

    public function store(SoalPemahamanRequest $request)
    {
        $data = $request->validated();
        $data['urutan'] ??= SoalPemahaman::where('materi_id', $data['materi_id'])->max('urutan') + 1;

        $soal = SoalPemahaman::create($data);

        return (new SoalPemahamanResource($soal))->response()->setStatusCode(201);
    }

    public function update(SoalPemahamanRequest $request, SoalPemahaman $soalPemahaman)
    {
        $soalPemahaman->update($request->validated());

        return new SoalPemahamanResource($soalPemahaman);
    }

    public function destroy(SoalPemahaman $soalPemahaman)
    {
        $soalPemahaman->delete();

        return response()->json(['message' => 'Soal pemahaman dihapus.']);
    }
}
