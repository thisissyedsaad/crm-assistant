<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add year and month columns
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->integer('year')->after('user_id')->default(now()->year);
            $table->integer('month')->after('year')->default(now()->month);
        });

        // Step 2: Update existing records with current year and month
        DB::table('daily_targets')->update([
            'year' => now()->year,
            'month' => now()->month,
        ]);

        // Step 3: Drop the old unique constraint on user_id and add new composite unique
        Schema::table('daily_targets', function (Blueprint $table) {
            $table->dropUnique(['user_id']); // Drop old unique constraint
            $table->unique(['user_id', 'year', 'month']); // Add new composite unique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_targets', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['user_id', 'year', 'month']);

            // Note: We cannot safely restore the old unique constraint
            // if there are multiple records per user now
            // $table->unique('user_id');
        });

        Schema::table('daily_targets', function (Blueprint $table) {
            $table->dropColumn(['year', 'month']);
        });
    }
};
