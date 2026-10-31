<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('type'); // fisik | digital
            $table->string('name');
            $table->text('description')->nullable();
            // khusus fisik
            $table->string('photo_path')->nullable();
            $table->string('storage_location')->nullable();
            // khusus digital
            $table->string('file_path')->nullable();      // nama acak di storage
            $table->string('original_name')->nullable();  // nama asli berkas
            $table->string('format')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('sha256_hash', 64)->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
