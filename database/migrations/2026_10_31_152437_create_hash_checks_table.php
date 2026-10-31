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
        Schema::create('hash_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained('evidence')->cascadeOnDelete();
            $table->foreignId('checked_by')->constrained('users');
            $table->string('computed_hash', 64);
            $table->string('stored_hash', 64);
            $table->boolean('is_match');
            $table->timestamp('checked_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hash_checks');
    }
};
