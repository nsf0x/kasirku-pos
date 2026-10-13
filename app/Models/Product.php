<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'cost_price',
        'price',
        'stock',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'price'      => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', 0);
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getMarginAttribute(): float
    {
        if ($this->price <= 0) return 0;
        return (($this->price - $this->cost_price) / $this->price) * 100;
    }

    public function getProfitAttribute(): float
    {
        return $this->price - $this->cost_price;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock && $this->stock > 0;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock <= 0;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
