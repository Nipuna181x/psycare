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
            $table->unsignedTinyInteger('phq9_total')->nullable()->after('pre_assessment_risk_level');
            $table->string('phq9_severity', 32)->nullable()->after('phq9_total');
            $table->unsignedTinyInteger('gad7_total')->nullable()->after('phq9_severity');
            $table->string('gad7_severity', 32)->nullable()->after('gad7_total');
            $table->boolean('self_harm_flag')->default(false)->after('gad7_severity');
            $table->boolean('requires_immediate_escalation')->default(false)->after('self_harm_flag');
            $table->text('screener_open_notes')->nullable()->after('requires_immediate_escalation');
            $table->timestamp('screener_completed_at')->nullable()->after('screener_open_notes');
            $table->index(['doctor_id', 'requires_immediate_escalation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['doctor_id', 'requires_immediate_escalation']);
            $table->dropColumn([
                'phq9_total', 'phq9_severity', 'gad7_total', 'gad7_severity', 'self_harm_flag',
                'requires_immediate_escalation', 'screener_open_notes', 'screener_completed_at',
            ]);
        });
    }
};
