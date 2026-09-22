<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SoalRequest;
use App\Http\Resources\SoalResource;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $query = Soal::with('opsiJawaban');

        if ($request->filled('kuis_id')) {
            $query->where('kuis_id', $request->integer('kuis_id'));
        }

        return SoalResource::collection($query->get());
    }

    public function store(SoalRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('soal', 'public');
        }

        $soal = DB::transaction(function () use ($data) {
            $soal = Soal::create(collect($data)->except('opsi_jawaban')->all());

            foreach ($data['opsi_jawaban'] ?? [] as $opsi) {
                $soal->opsiJawaban()->create($opsi);
            }

            return $soal;
        });

        return (new SoalResource($soal->load('opsiJawaban')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(SoalRequest $request, Soal $soal)
    {
        $data = $request->validated();

        if ($request->hasFile('gambar')) {
            if ($soal->gambar) {
                Storage::disk('public')->delete($soal->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('soal', 'public');
        }

        DB::transaction(function () use ($soal, $data) {
            $soal->update(collect($data)->except('opsi_jawaban')->all());

            $soal->opsiJawaban()->delete();
            foreach ($data['opsi_jawaban'] ?? [] as $opsi) {
                $soal->opsiJawaban()->create($opsi);
            }
        });

        return new SoalResource($soal->load('opsiJawaban'));
    }

    public function destroy(Soal $soal)
    {
        if ($soal->gambar) {
            Storage::disk('public')->delete($soal->gambar);
        }

        $soal->delete();

        return response()->json(['message' => 'Soal dihapus.']);
    }
}
