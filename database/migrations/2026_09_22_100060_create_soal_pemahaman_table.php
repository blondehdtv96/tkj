<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kuis pemahaman singkat yang menempel pada satu materi. Sengaja dipisahkan
 * dari tabel soal/opsi_jawaban milik kuis bab supaya bank soal penilaian tidak
 * tercampur dengan pertanyaan pengecek bacaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal_pemahaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materi_id')->constrained('materi')->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->json('opsi');
            $table->unsignedTinyInteger('jawaban_benar');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_pemahaman');
    }
};
