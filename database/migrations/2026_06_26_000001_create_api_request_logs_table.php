<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint', 100);        // short label e.g. "orders/planned"
            $table->string('full_url', 600);        // actual URL with query string
            $table->string('method', 10)->default('GET');
            $table->integer('status_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->string('triggered_by', 100)->nullable(); // e.g. "notifications", "datatable", "counters"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};