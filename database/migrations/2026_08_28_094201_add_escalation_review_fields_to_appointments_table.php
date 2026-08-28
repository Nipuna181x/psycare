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
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('escalation_reviewed')->default(false)->after('requires_immediate_escalation');
            $table->timestamp('escalation_reviewed_at')->nullable()->after('escalation_reviewed');
            $table->index(['doctor_id', 'escalation_reviewed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['doctor_id', 'escalation_reviewed']);
            $table->dropColumn(['escalation_reviewed', 'escalation_reviewed_at']);
        });
    }
};
