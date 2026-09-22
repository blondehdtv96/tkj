<?php

namespace App\Exports;

use App\Models\HasilKuis;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NilaiExport implements FromCollection, WithHeadings
{
    public function __construct(private ?int $kelasId = null, private ?int $kuisId = null)
    {
    }

    public function collection()
    {
        $query = HasilKuis::with(['user.kelas', 'kuis.bab'])->whereNotNull('waktu_selesai');

        if ($this->kelasId) {
            $query->whereHas('user', fn ($q) => $q->where('kelas_id', $this->kelasId));
        }

        if ($this->kuisId) {
            $query->where('kuis_id', $this->kuisId);
        }

        return $query->orderBy('waktu_selesai', 'desc')->get()->map(fn (HasilKuis $hasil) => [
            'Nama Siswa' => $hasil->user->name,
            'NIS' => $hasil->user->nis,
            'Kelas' => $hasil->user->kelas->nama_rombel ?? '-',
            'Bab' => $hasil->kuis->bab->judul ?? '-',
            'Kuis' => $hasil->kuis->judul,
            'Skor' => $hasil->skor,
            'KKM' => $hasil->kuis->kkm,
            'Status' => $hasil->skor >= $hasil->kuis->kkm ? 'Lulus' : 'Belum Lulus',
            'Waktu Selesai' => optional($hasil->waktu_selesai)->format('Y-m-d H:i'),
        ]);
    }

    public function headings(): array
    {
        return ['Nama Siswa', 'NIS', 'Kelas', 'Bab', 'Kuis', 'Skor', 'KKM', 'Status', 'Waktu Selesai'];
    }
}
