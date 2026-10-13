<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'sales_status_created_at_index');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('created_at', 'stock_movements_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_created_at_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_status_created_at_index');
        });
    }
};
