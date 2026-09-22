<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BabRequest;
use App\Http\Resources\BabResource;
use App\Models\Bab;
use App\Models\ProgresMateri;
use Illuminate\Http\Request;

class BabController extends Controller
{
    public function index(Request $request)
    {
        $query = Bab::withCount('materi')->with(['materi', 'kuis.bab.materi', 'referensiBelajar']);

        if ($request->filled('mata_pelajaran_id')) {
            $query->where('mata_pelajaran_id', $request->integer('mata_pelajaran_id'));
        }

        $babList = $query->orderBy('urutan')->get();

        ProgresMateri::tandaiStatusSelesai($request->user(), $babList->flatMap->materi);

        return BabResource::collection($babList);
    }

    public function show(Request $request, Bab $bab)
    {
        $bab->load(['materi', 'kuis.bab.materi', 'referensiBelajar']);

        ProgresMateri::tandaiStatusSelesai($request->user(), $bab->materi);

        return new BabResource($bab);
    }

    public function store(BabRequest $request)
    {
        $bab = Bab::create($request->validated());

        return (new BabResource($bab))->response()->setStatusCode(201);
    }

    public function update(BabRequest $request, Bab $bab)
    {
        $bab->update($request->validated());

        return new BabResource($bab);
    }

    public function destroy(Bab $bab)
    {
        $bab->delete();

        return response()->json(['message' => 'Bab dihapus.']);
    }
}
