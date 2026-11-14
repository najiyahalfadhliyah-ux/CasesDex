<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tahap 6: pihak terkait dan peristiwa kronologi bisa dinonaktifkan (tidak dihapus).
return new class extends Migration
{
    public function up(): void
    {
        foreach (['suspects', 'timeline_events'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->boolean('is_active')->default(true));
        }
    }

    public function down(): void
    {
        foreach (['suspects', 'timeline_events'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('is_active'));
        }
    }
};
