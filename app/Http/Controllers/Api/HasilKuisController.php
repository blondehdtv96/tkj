<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HasilKuisResource;
use App\Models\HasilKuis;
use Illuminate\Http\Request;

class HasilKuisController extends Controller
{
    public function index(Request $request)
    {
        $query = HasilKuis::with(['kuis.bab.materi', 'user.kelas'])->whereNotNull('waktu_selesai');

        if ($request->user()->role === 'siswa') {
            $query->where('user_id', $request->user()->id);
        } else {
            if ($request->filled('kuis_id')) {
                $query->where('kuis_id', $request->integer('kuis_id'));
            }

            if ($request->filled('kelas_id')) {
                $query->whereHas('user', fn ($q) => $q->where('kelas_id', $request->integer('kelas_id')));
            }

            if ($request->filled('di_bawah_kkm')) {
                $query->whereHas('kuis', fn ($q) => $q->whereColumn('kuis.kkm', '>', 'hasil_kuis.skor'));
            }
        }

        $sortBy = in_array($request->get('sort_by'), ['skor', 'waktu_selesai']) ? $request->get('sort_by') : 'waktu_selesai';
        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';

        return HasilKuisResource::collection($query->orderBy($sortBy, $sortDir)->paginate(20));
    }

    public function show(Request $request, HasilKuis $hasilKuis)
    {
        if ($request->user()->role === 'siswa' && $hasilKuis->user_id !== $request->user()->id) {
            abort(403);
        }

        return new HasilKuisResource($hasilKuis->load(['jawabanSiswa.soal.opsiJawaban', 'kuis.bab.materi', 'user']));
    }
}
