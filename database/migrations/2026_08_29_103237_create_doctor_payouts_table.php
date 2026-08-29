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
        Schema::create('doctor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('medical_centers')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->string('marked_by_type');
            $table->unsignedBigInteger('marked_by_id');
            $table->string('marked_by_name');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('payment_count');
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index(['clinic_id', 'doctor_id', 'paid_at']);
            $table->index(['marked_by_type', 'marked_by_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_payouts');
    }
};
