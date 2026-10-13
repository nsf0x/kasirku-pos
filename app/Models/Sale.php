<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'user_id',
        'subtotal',
        'discount',
        'total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'void_reason',
        'voided_by',
        'voided_at',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'total'         => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'change_amount' => 'decimal:2',
        'voided_at'     => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeVoid($query)
    {
        return $query->where('status', 'void');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getGrossProfitAttribute(): float
    {
        return $this->items->sum(fn($item) => ($item->price - $item->cost_price) * $item->qty)
            - (float) $this->discount;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voidedByUser()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
