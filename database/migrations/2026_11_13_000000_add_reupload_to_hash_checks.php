<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Riwayat unggah ulang disimpan bersama riwayat pemeriksaan di hash_checks.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hash_checks', function (Blueprint $table) {
            $table->string('action')->default('periksa')->after('checked_by'); // periksa | unggah_ulang
            $table->string('file_path')->nullable()->after('action');          // berkas yang diperiksa / diunggah
            $table->string('original_name')->nullable()->after('file_path');   // khusus unggah ulang
            $table->unsignedBigInteger('size')->nullable()->after('original_name');
        });
    }

    public function down(): void
    {
        Schema::table('hash_checks', function (Blueprint $table) {
            $table->dropColumn(['action', 'file_path', 'original_name', 'size']);
        });
    }
};
