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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->foreignId('clinic_id')->constrained('medical_centers')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('doctor_payout_id')->nullable()->constrained('doctor_payouts')->nullOnDelete();
            $table->string('stripe_session_id')->unique();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('lkr');
            $table->decimal('doctor_amount', 12, 2);
            $table->decimal('clinic_amount', 12, 2);
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded'])->default('pending');
            $table->enum('doctor_payout_status', ['unpaid', 'paid'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->timestamp('doctor_paid_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('notifications_sent_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['clinic_id', 'status', 'created_at']);
            $table->index(['clinic_id', 'doctor_id', 'doctor_payout_status']);
            $table->index(['doctor_id', 'status', 'doctor_payout_status']);
            $table->index(['patient_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
