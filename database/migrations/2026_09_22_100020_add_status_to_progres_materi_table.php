<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel progres_materi sudah menjadi material_progress milik aplikasi ini,
 * sehingga modul gamifikasi memakainya kembali daripada membuat tabel kembar.
 * Yang ditambahkan hanya kolom status agar materi yang baru dibuka dapat
 * dibedakan dari materi yang benar-benar tuntas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progres_materi', function (Blueprint $table) {
            $table->enum('status', ['dibaca', 'selesai'])->default('selesai')->after('materi_id');
        });
    }

    public function down(): void
    {
        Schema::table('progres_materi', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
