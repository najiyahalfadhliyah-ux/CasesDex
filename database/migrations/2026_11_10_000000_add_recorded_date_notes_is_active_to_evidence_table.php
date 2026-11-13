<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kolom tambahan Tahap 5. Migration lama tidak diubah.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence', function (Blueprint $table) {
            $table->date('recorded_date')->nullable()->after('type'); // tanggal pencatatan
            $table->text('notes')->nullable()->after('description');  // keterangan
            $table->boolean('is_active')->default(true)->after('sha256_hash');
        });
    }

    public function down(): void
    {
        Schema::table('evidence', function (Blueprint $table) {
            $table->dropColumn(['recorded_date', 'notes', 'is_active']);
        });
    }
};
