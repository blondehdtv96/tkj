<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referensi_belajar', function (Blueprint $table) {
            $table->dropColumn(['pdf_judul', 'pdf_url']);
            $table->longText('ringkasan')->nullable()->after('bab_id');
        });
    }

    public function down(): void
    {
        Schema::table('referensi_belajar', function (Blueprint $table) {
            $table->dropColumn('ringkasan');
            $table->string('pdf_judul')->nullable()->after('bab_id');
            $table->string('pdf_url')->nullable()->after('pdf_judul');
        });
    }
};
