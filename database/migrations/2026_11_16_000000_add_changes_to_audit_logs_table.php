<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nilai sebelum/sesudah per field yang berubah, mis. {"status": {"old": "selesai", "new": "ditutup"}}
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->json('changes')->nullable()->after('summary');
            $table->index('created_at'); // daftar diurut & difilter berdasarkan waktu
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropColumn('changes');
        });
    }
};
