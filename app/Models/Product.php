<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'original_stock',
        'is_flash_sale',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'original_stock' => 'integer',
        'is_flash_sale' => 'boolean',
    ];

    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get available stock considering active holds
     */
    public function getAvailableStockAttribute(): int
    {
        $activeHoldsQty = $this->holds()
            ->where('expires_at', '>', now())
            ->whereNull('order_id')
            ->sum('quantity');

        return max(0, $this->stock - $activeHoldsQty);
    }

    /**
     * Atomic stock decrement with lock
     */
    public function decrementStock(int $quantity): bool
    {
        return $this->where('id', $this->id)
            ->where('stock', '>=', $quantity)
            ->decrement('stock', $quantity);
    }

    /**
     * Atomic stock increment with lock
     */
    public function incrementStock(int $quantity): bool
    {
        return $this->where('id', $this->id)
            ->increment('stock', $quantity);
    }
}
