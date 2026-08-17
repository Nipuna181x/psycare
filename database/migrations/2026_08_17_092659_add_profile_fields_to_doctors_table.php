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
        Schema::table('doctors', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('specialization');
            $table->unsignedSmallInteger('years_experience')->nullable()->after('bio');
            $table->decimal('consultation_fee', 8, 2)->nullable()->after('years_experience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['bio', 'years_experience', 'consultation_fee']);
        });
    }
};
