<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_sequences', function (Blueprint $table) {
            $table->string('sequence_date', 8)->primary();
            $table->unsignedInteger('sequence')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_sequences');
    }
};
