<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('sku', 50)->unique()->comment('SKU / barcode');
            $table->string('name', 150);
            $table->decimal('cost_price', 12, 2)->default(0)->comment('harga beli');
            $table->decimal('price', 12, 2)->default(0)->comment('harga jual');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5)->comment('batas notifikasi stok menipis');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
