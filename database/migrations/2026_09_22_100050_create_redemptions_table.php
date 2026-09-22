<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reward_id')->constrained('rewards')->cascadeOnDelete();

            // Disalin saat pengajuan agar riwayat tidak berubah ketika harga reward direvisi.
            $table->unsignedInteger('jumlah_poin');
            $table->enum('status', ['pending', 'approved', 'completed', 'rejected'])->default('pending');
            $table->string('catatan_siswa')->nullable();
            $table->string('catatan_petugas')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_pada')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
};
