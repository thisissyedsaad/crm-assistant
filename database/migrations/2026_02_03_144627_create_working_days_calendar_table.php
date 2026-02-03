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
        Schema::create('working_days_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year'); // e.g., 2026
            $table->integer('month'); // 1-12
            $table->json('working_days'); // Array of selected dates [1, 2, 5, 8, ...]
            $table->integer('total_working_days')->default(0); // Count of working days
            $table->timestamps();

            // Unique constraint: one record per user per month per year
            $table->unique(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_days_calendar');
    }
};
