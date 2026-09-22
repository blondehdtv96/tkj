<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->enum('tipe', ['pilihan_ganda', 'essay', 'benar_salah'])->default('pilihan_ganda');
            $table->string('gambar')->nullable();
            $table->text('pembahasan')->nullable();
            $table->unsignedInteger('bobot')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal');
    }
};
