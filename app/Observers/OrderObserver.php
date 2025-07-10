<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function creating(Order $order): void
    {
        $order->order_number = self::generateOrderNumber();

    }

    private static function generateOrderNumber(): string
    {
        return uniqid(prefix: 'ORD-');
    }
}
