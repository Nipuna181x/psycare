<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY status ENUM('pending_payment', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'confirmed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY status ENUM('confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'confirmed'");
    }
};
