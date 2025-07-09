<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemOption extends Model
{
    protected $fillable = [
        'order_item_id',
        'product_option_group_id',
        'product_option_id',
        'group_name',
        'group_type',
        'group_is_required',
        'option_name',
        'option_description',
        'option_extra_price',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(ProductOptionGroup::class, 'product_option_group_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    protected function casts(): array
    {
        return [
            'group_is_required' => 'boolean',
            'option_extra_price' => 'decimal:2',
        ];
    }
}
