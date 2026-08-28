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
        Schema::create('doctor_clinic_affiliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('medical_centers')->cascadeOnDelete();
            $table->enum('status', ['requested', 'accepted', 'declined', 'active', 'ended'])->default('requested');
            $table->timestamp('requested_by_clinic_at')->nullable();
            $table->timestamp('responded_by_doctor_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'clinic_id']);
            $table->index(['doctor_id', 'status']);
            $table->index(['clinic_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_clinic_affiliations');
    }
};
