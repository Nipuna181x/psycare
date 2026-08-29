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
        Schema::table('doctor_payouts', function (Blueprint $table) {
            $table->enum('status', ['paid', 'completed'])->default('paid')->after('paid_at');
            $table->timestamp('received_at')->nullable()->after('status');
            $table->index(['doctor_id', 'status', 'paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_payouts', function (Blueprint $table) {
            $table->dropIndex(['doctor_id', 'status', 'paid_at']);
            $table->dropColumn(['status', 'received_at']);
        });
    }
};
