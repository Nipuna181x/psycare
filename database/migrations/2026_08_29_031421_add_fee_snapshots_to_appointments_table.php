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
            $table->decimal('doctor_fee_charged', 8, 2)->nullable()->after('consultation_fee');
            $table->decimal('clinic_fee_charged', 8, 2)->nullable()->after('doctor_fee_charged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['doctor_fee_charged', 'clinic_fee_charged']);
        });
    }
};
