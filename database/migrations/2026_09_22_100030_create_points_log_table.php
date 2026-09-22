<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('sumber', [
                'materi',
                'kuis',
                'bonus_kkm',
                'bonus_tepat_waktu',
                'bonus_bab',
                'streak',
                'redeem',
                'refund',
                'penyesuaian',
            ]);
            $table->integer('jumlah');
            $table->string('keterangan');

            // Kunci idempotensi per siswa, mis. "materi:12" atau "streak:2026-09-22".
            // Dibiarkan null untuk perolehan yang memang boleh berulang.
            $table->string('kunci_unik')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'kunci_unik']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_log');
    }
};
