<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('product_name', 150)->comment('snapshot nama saat transaksi');
            $table->integer('qty');
            $table->decimal('price', 12, 2)->comment('snapshot harga jual');
            $table->decimal('cost_price', 12, 2)->comment('snapshot harga beli');
            $table->decimal('subtotal', 12, 2);

            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
