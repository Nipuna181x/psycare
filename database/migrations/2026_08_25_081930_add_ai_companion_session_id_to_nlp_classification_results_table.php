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
        Schema::table('nlp_classification_results', function (Blueprint $table) {
            $table->foreignId('ai_companion_session_id')->nullable()->after('patient_id')
                ->constrained('ai_companion_sessions')->nullOnDelete();
            $table->unique('ai_companion_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nlp_classification_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ai_companion_session_id');
        });
    }
};
