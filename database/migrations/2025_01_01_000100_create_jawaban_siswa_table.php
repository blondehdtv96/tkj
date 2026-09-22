<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_kuis_id')->constrained('hasil_kuis')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
            $table->foreignId('opsi_id')->nullable()->constrained('opsi_jawaban')->nullOnDelete();
            $table->text('jawaban_teks')->nullable();
            $table->boolean('is_benar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswa');
    }
};
