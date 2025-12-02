<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hold extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'expires_at',
        'order_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'quantity' => 'integer',
    ];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && is_null($this->order_id);
    }

    /**
     * Release hold and return stock
     */
    public function release(): void
    {
        if ($this->product && $this->quantity > 0) {
            $this->product->incrementStock($this->quantity);
        }

        $this->delete();
    }
}
