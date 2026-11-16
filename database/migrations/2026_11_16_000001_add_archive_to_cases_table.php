<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tahap 8: status arsip terpisah dari status penanganan. Diarsipkan manual oleh admin (tanpa job otomatis).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('archive_status')->default('aktif')->index()->after('archive_deadline'); // aktif|diarsipkan
            $table->date('archived_at')->nullable()->after('archive_status');
            $table->string('archive_reason')->nullable()->after('archived_at');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex(['archive_status']);
            $table->dropColumn(['archive_status', 'archived_at', 'archive_reason']);
        });
    }
};
