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
        Schema::create('hidden_current_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('order_id'); // The order ID from the third-party API
            $table->string('hidden_by')->default('guest'); // User ID or 'guest' for non-authenticated users
            $table->timestamp('hidden_at');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['order_id', 'hidden_by']);
            $table->index(['hidden_by', 'created_at']);
            $table->index('hidden_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hidden_current_jobs');    
    }
};
