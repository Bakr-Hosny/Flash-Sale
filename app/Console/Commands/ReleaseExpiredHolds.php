<?php

namespace App\Console\Commands;

use App\Models\Hold;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredHolds extends Command
{
    protected $signature = 'holds:release-expired';
    protected $description = 'Release expired holds and return stock';

    public function handle(): void
    {

        $holds = Hold::with('product')
            ->where('expires_at', '<=', now())
            ->whereNull('order_id')
            ->get();




        $releasedCount = 0;
        foreach ($holds as $hold) {
            $product = $hold->product;
            if ($product) {
                // Return stock to product
                $product->increment('stock', $hold->quantity);
                Log::info('Stock returned for expired hold', [
                    'hold_id' => $hold->id,
                    'product_id' => $product->id,
                    'returned_quantity' => $hold->quantity,
                    'new_stock' => $product->stock,
                ]);
            }
            // Delete the hold
            $hold->delete();
            $releasedCount++;
        }


        Log::info('Expired holds released', [
            'count' => $releasedCount,
            'executed_at' => now()->toISOString(),
        ]);

        $this->info("Released {$releasedCount} expired holds.");
    }
}
