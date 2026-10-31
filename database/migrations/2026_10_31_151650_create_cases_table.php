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
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('title');
            $table->string('category');
            $table->date('incident_date');
            $table->date('recorded_date');
            $table->date('investigation_start_date');
            $table->date('completed_date')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->text('summary')->nullable();
            $table->text('suspected_motive')->nullable();
            $table->string('status')->default('dalam_penanganan');
            $table->date('archive_deadline');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
