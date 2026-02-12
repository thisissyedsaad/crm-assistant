<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration updates working days columns to support decimal values (0.5 for half days)
     */
    public function up(): void
    {
        // Update working_days_calendar table
        Schema::table('working_days_calendar', function (Blueprint $table) {
            // Change total_working_days from integer to decimal to support half days (e.g., 18.5)
            $table->decimal('total_working_days', 5, 1)->default(0)->change();
        });

        // Update daily_targets table
        Schema::table('daily_targets', function (Blueprint $table) {
            // Change working_days from integer to decimal
            $table->decimal('working_days', 5, 1)->default(0)->change();
            // Change monthly_target to decimal to support fractional calculations
            $table->decimal('monthly_target', 8, 1)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('working_days_calendar', function (Blueprint $table) {
            $table->integer('total_working_days')->default(0)->change();
        });

        Schema::table('daily_targets', function (Blueprint $table) {
            $table->integer('working_days')->default(0)->change();
            $table->integer('monthly_target')->default(0)->change();
        });
    }
};
