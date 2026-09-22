<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReferensiBelajarRequest;
use App\Http\Resources\ReferensiBelajarResource;
use App\Models\ReferensiBelajar;
use Illuminate\Http\Request;

class ReferensiBelajarController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferensiBelajar::query();

        if ($request->filled('bab_id')) {
            $query->where('bab_id', $request->integer('bab_id'));
        }

        return ReferensiBelajarResource::collection($query->get());
    }

    public function store(ReferensiBelajarRequest $request)
    {
        $referensi = ReferensiBelajar::updateOrCreate(
            ['bab_id' => $request->validated('bab_id')],
            $request->validated()
        );

        return (new ReferensiBelajarResource($referensi))->response()->setStatusCode(201);
    }

    public function destroy(ReferensiBelajar $referensiBelajar)
    {
        $referensiBelajar->delete();

        return response()->json(['message' => 'Referensi belajar dihapus.']);
    }
}
