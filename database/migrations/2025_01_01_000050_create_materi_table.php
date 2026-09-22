<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bab_id')->constrained('bab')->cascadeOnDelete();
            $table->string('judul');
            $table->longText('konten_html')->nullable();
            $table->enum('tipe', ['teks', 'video', 'pdf'])->default('teks');
            $table->string('file_path')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi');
    }
};
