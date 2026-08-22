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
        Schema::create('ai_companion_turns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_companion_session_id')->constrained()->cascadeOnDelete();
            $table->string('role', 16);
            $table->unsignedSmallInteger('sequence');
            $table->text('content');
            $table->timestamps();
            $table->unique(['ai_companion_session_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_companion_turns');
    }
};
