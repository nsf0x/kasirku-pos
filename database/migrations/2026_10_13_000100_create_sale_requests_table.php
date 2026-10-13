<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_requests', function (Blueprint $table) {
            $table->uuid('request_key')->primary();
            $table->foreignId('sale_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_requests');
    }
};
