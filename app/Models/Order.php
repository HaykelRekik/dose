<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'branch_id',
        'status',
        'total_price',
        'estimated_preparation_time',
        'ready_at',
        'payment_method',
        'payment_reference',
        'payment_provider',
        'customer_note',
        'products_snapshot',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Deleted User',
        ]);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function canTransitionTo(OrderStatus $status): bool
    {
        return $this->status->canTransitionTo($status);
    }

    public function setReadyAt(): void
    {
        $this->update(['ready_at' => now()]);
    }

    public function markAsReady(): void
    {
        if ($this->canTransitionTo(OrderStatus::READY)) {
            $this->update([
                'status' => OrderStatus::READY,
                'ready_at' => now(),
            ]);
        }
    }

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
            'total_price' => 'decimal:2',
            'ready_at' => 'datetime',
            'products_snapshot' => 'array',
        ];
    }
}
