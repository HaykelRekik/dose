<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class OrderObserver
{
    public function creating(Order $order): void
    {
        $order->order_number = self::generateOrderNumber();

    }

    private static function generateOrderNumber(): string
    {
        $cacheKey = 'order_counter_' . now()->format('Ymd');

        $counter = Cache::remember($cacheKey, 3600, fn () => Order::whereDate('created_at', today())->count() + 1);

        $counter = Cache::increment($cacheKey);

        return mb_str_pad((string) $counter, 8, '0', STR_PAD_LEFT);
    }
}
