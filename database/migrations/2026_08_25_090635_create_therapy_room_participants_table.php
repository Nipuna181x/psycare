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
        Schema::create('therapy_room_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapy_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->string('anonymous_label', 32);
            $table->unsignedInteger('join_order');
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();

            $table->unique(['therapy_room_id', 'patient_id']);
            $table->unique(['therapy_room_id', 'anonymous_label']);
            $table->unique(['therapy_room_id', 'join_order']);
            $table->index(['therapy_room_id', 'join_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('therapy_room_participants');
    }
};
