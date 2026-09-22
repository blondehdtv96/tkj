<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('total_poin')->default(0)->after('kelas_id');
            $table->string('level')->default('Pemula')->after('total_poin');
            $table->unsignedInteger('streak_hari')->default(0)->after('level');
            $table->date('streak_terakhir')->nullable()->after('streak_hari');

            $table->index('total_poin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['total_poin']);
            $table->dropColumn(['total_poin', 'level', 'streak_hari', 'streak_terakhir']);
        });
    }
};
