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
        Schema::create('nlp_classification_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->date('entry_date');
            $table->enum('risk_level', ['low', 'moderate', 'high', 'urgent']);
            $table->boolean('self_harm_flag')->default(false);
            $table->decimal('self_harm_confidence', 4, 3)->nullable();
            $table->string('phq9_severity')->nullable();
            $table->string('gad7_severity')->nullable();
            $table->json('symptoms')->nullable();
            $table->json('symptom_scores')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'entry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nlp_classification_results');
    }
};
