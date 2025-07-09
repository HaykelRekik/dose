<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_base_price',
        'quantity',
        'item_total_price',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault([
            'name' => $this->product_name ?? 'Deleted Product',
        ]);
    }

    public function options(): HasMany
    {
        return $this->hasMany(OrderItemOption::class);
    }

    protected function casts(): array
    {
        return [
            'product_base_price' => 'decimal:2',
            'item_total_price' => 'decimal:2',
        ];
    }
}
