<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MateriRequest;
use App\Http\Requests\MateriSelesaiRequest;
use App\Http\Resources\MateriResource;
use App\Http\Resources\PointsLogResource;
use App\Models\Materi;
use App\Models\ProgresMateri;
use App\Services\Gamifikasi\PemahamanService;
use App\Services\Gamifikasi\PoinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MateriController extends Controller
{
    public function __construct(
        private PoinService $poinService,
        private PemahamanService $pemahamanService,
    ) {
    }

    public function index(Request $request)
    {
        $query = Materi::query();

        if ($request->filled('bab_id')) {
            $query->where('bab_id', $request->integer('bab_id'));
        }

        $materi = $query->withCount('soalPemahaman')->orderBy('urutan')->get();

        ProgresMateri::tandaiStatusSelesai($request->user(), $materi);

        return MateriResource::collection($materi);
    }

    public function show(Request $request, Materi $materi)
    {
        $materi->loadCount('soalPemahaman');

        ProgresMateri::tandaiStatusSelesai($request->user(), collect([$materi]));

        $this->catatDibaca($request, $materi);

        return new MateriResource($materi);
    }

    public function store(MateriRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('materi', 'public');
        }

        $materi = Materi::create($data);

        return (new MateriResource($materi))->response()->setStatusCode(201);
    }

    public function update(MateriRequest $request, Materi $materi)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            if ($materi->file_path) {
                Storage::disk('public')->delete($materi->file_path);
            }
            $data['file_path'] = $request->file('file')->store('materi', 'public');
        }

        $materi->update($data);

        return new MateriResource($materi);
    }

    public function destroy(Materi $materi)
    {
        if ($materi->file_path) {
            Storage::disk('public')->delete($materi->file_path);
        }

        $materi->delete();

        return response()->json(['message' => 'Materi dihapus.']);
    }

    /**
     * Menandai materi selesai. Bila materi punya soal pemahaman, jawabannya
     * diperiksa lebih dulu agar poin membaca tidak bisa didapat dengan
     * sekadar menekan tombol.
     */
    public function tandaiSelesai(MateriSelesaiRequest $request, Materi $materi)
    {
        $pemahaman = $this->pemahamanService->periksa($materi, $request->validated('jawaban') ?? []);

        ProgresMateri::updateOrCreate(
            ['user_id' => $request->user()->id, 'materi_id' => $materi->id],
            ['status' => 'selesai', 'selesai_pada' => now()]
        );

        $poin = $this->poinService->dariMateri($request->user(), $materi->loadMissing('bab'));

        return response()->json([
            'message' => 'Materi ditandai selesai.',
            'pemahaman' => $pemahaman,
            'poin' => PointsLogResource::collection($poin),
            'poin_diperoleh' => $poin->sum('jumlah'),
            'total_poin' => (int) $request->user()->fresh()->total_poin,
        ]);
    }

    /**
     * Mencatat bahwa siswa membuka materi. Berstatus "dibaca" sehingga tidak
     * ikut membuka kunci kuis dan tidak menghasilkan poin.
     */
    private function catatDibaca(Request $request, Materi $materi): void
    {
        if ($request->user()->role !== 'siswa') {
            return;
        }

        ProgresMateri::firstOrCreate(
            ['user_id' => $request->user()->id, 'materi_id' => $materi->id],
            ['status' => 'dibaca']
        );
    }
}
