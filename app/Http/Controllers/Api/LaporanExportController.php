<?php

namespace App\Http\Controllers\Api;

use App\Exports\NilaiExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanExportController extends Controller
{
    public function exportNilai(Request $request)
    {
        $export = new NilaiExport(
            $request->integer('kelas_id') ?: null,
            $request->integer('kuis_id') ?: null,
        );

        return Excel::download($export, 'rekap-nilai-' . now()->format('Y-m-d-His') . '.xlsx');
    }
}
