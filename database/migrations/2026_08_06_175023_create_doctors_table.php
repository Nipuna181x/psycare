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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('license_number')->unique();
            $table->string('phone')->nullable();
            $table->string('specialization')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_photo')->nullable();
            $table->unsignedSmallInteger('years_of_experience')->nullable();
            $table->decimal('consultation_fee', 8, 2)->nullable();
            $table->enum('consultation_mode', ['in_person', 'online', 'both'])->default('both');
            $table->decimal('rating', 3, 1)->nullable();
            $table->enum('status', ['pending_approval', 'approved', 'rejected', 'suspended'])->default('pending_approval');
            $table->enum('onboarding_step', ['basic_info_done', 'profile_complete'])->default('basic_info_done');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
