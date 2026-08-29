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
        Schema::create('mood_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('mood_score');
            $table->json('mood_tags');
            $table->decimal('sleep_hours', 4, 1)->nullable();
            $table->text('note')->nullable();
            $table->date('entry_date');
            $table->timestamps();

            $table->unique(['patient_id', 'entry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mood_entries');
    }
};
