<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah tipe soal "troubleshooting" (menyusun langkah penanganan sesuai
 * urutan) sekaligus membuka pengaturan poin gamifikasi per soal dan per materi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soal', function (Blueprint $table) {
            // Poin gamifikasi yang diperoleh bila soal dijawab benar.
            // Null berarti memakai nilai bawaan dari config/gamifikasi.php.
            $table->unsignedInteger('poin')->nullable()->after('bobot');
            // Latar situasi untuk soal troubleshooting.
            $table->text('skenario')->nullable()->after('pertanyaan');
        });

        Schema::table('opsi_jawaban', function (Blueprint $table) {
            // Posisi langkah yang benar pada soal troubleshooting.
            $table->unsignedInteger('urutan')->nullable()->after('is_benar');
        });

        Schema::table('jawaban_siswa', function (Blueprint $table) {
            // Urutan opsi yang disusun siswa pada soal troubleshooting.
            $table->json('jawaban_urutan')->nullable()->after('jawaban_teks');
            // Bagian benar untuk penilaian sebagian, 0..1.
            $table->decimal('porsi_benar', 5, 4)->nullable()->after('is_benar');
        });

        Schema::table('materi', function (Blueprint $table) {
            $table->unsignedInteger('poin')->nullable()->after('urutan');
        });

        $this->ubahEnumTipeSoal(['pilihan_ganda', 'essay', 'benar_salah', 'troubleshooting']);
        $this->isiPoinSoalLama();
    }

    public function down(): void
    {
        $this->ubahEnumTipeSoal(['pilihan_ganda', 'essay', 'benar_salah']);

        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn(['poin', 'skenario']);
        });

        Schema::table('opsi_jawaban', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });

        Schema::table('jawaban_siswa', function (Blueprint $table) {
            $table->dropColumn(['jawaban_urutan', 'porsi_benar']);
        });

        Schema::table('materi', function (Blueprint $table) {
            $table->dropColumn('poin');
        });
    }

    /**
     * MySQL menyimpan enum sebagai tipe kolom, SQLite sebagai CHECK constraint.
     * change() menangani keduanya, jadi daftar tipe tidak perlu ditulis per driver.
     *
     * @param  array<int, string>  $tipe
     */
    private function ubahEnumTipeSoal(array $tipe): void
    {
        Schema::table('soal', function (Blueprint $table) use ($tipe) {
            $table->enum('tipe', $tipe)->default('pilihan_ganda')->change();
        });
    }

    /**
     * Soal yang sudah ada diberi poin sehingga total satu kuis tetap sebesar
     * poin maksimal kuis sebelum modul ini — perilaku lama tidak berubah.
     */
    private function isiPoinSoalLama(): void
    {
        $maksimal = (int) config('gamifikasi.poin.kuis_maksimal', 50);

        DB::table('soal')
            ->select('kuis_id', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('kuis_id')
            ->get()
            ->each(function ($baris) use ($maksimal) {
                $poin = $baris->jumlah > 0 ? (int) round($maksimal / $baris->jumlah) : $maksimal;

                DB::table('soal')->where('kuis_id', $baris->kuis_id)->update(['poin' => max(1, $poin)]);
            });
    }
};
