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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medical_center_id')->constrained()->cascadeOnDelete();

            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('mode', ['in_person', 'online'])->default('in_person');

            $table->string('patient_name');
            $table->unsignedTinyInteger('patient_age')->nullable();
            $table->enum('patient_gender', ['male', 'female', 'other'])->nullable();
            $table->string('patient_phone');
            $table->string('patient_email')->nullable();
            $table->text('reason')->nullable();

            $table->decimal('consultation_fee', 8, 2)->nullable();

            $table->json('pre_assessment')->nullable();
            $table->unsignedTinyInteger('pre_assessment_mood_rating')->nullable();
            $table->text('pre_assessment_summary')->nullable();
            $table->enum('pre_assessment_risk_level', ['low', 'moderate', 'elevated'])->nullable();

            $table->enum('status', ['confirmed', 'completed', 'cancelled'])->default('confirmed');

            $table->timestamps();

            $table->index(['doctor_id', 'appointment_date', 'appointment_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
