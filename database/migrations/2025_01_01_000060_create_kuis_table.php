<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bab_id')->constrained('bab')->cascadeOnDelete();
            $table->string('judul');
            $table->unsignedInteger('durasi_menit')->default(30);
            $table->unsignedTinyInteger('kkm')->default(75);
            $table->boolean('acak_soal')->default(true);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuis');
    }
};
