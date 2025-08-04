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
        Schema::create('current_jobs_trackings', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->bigInteger('order_id')->unsigned()->unique(); // Third-party order ID as number

            // Three confirmation columns
            $table->boolean('collection_checked_in')->default(false);
            $table->boolean('driver_eta_confirmed')->default(false);
            $table->boolean('midpoint_check_completed')->default(false);
            $table->integer('delivered')->nullable();
            
            // Status: pending or completed
            $table->enum('status', ['pending', 'completed'])->default('pending');
            
            // Store full third-party order data as JSON
            $table->json('order_data')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_jobs_trackings');
    }
};
