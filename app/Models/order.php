<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'product_id',
        'hold_id',
        'quantity',
        'total_price',
        'status',
        'payment_reference',
        'idempotency_key',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function hold(): BelongsTo
    {
        return $this->belongsTo(Hold::class);
    }

    public function markAsPaid(string $paymentReference): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_reference' => $paymentReference,
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);

        // Release stock if hold still exists
        if ($this->hold) {
            $this->hold->release();
        }
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}
