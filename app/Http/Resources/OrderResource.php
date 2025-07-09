<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'customer' => $this->customer->name,
            'total_price' => $this->total_price,
            'estimated_preparation_time' => $this->estimated_preparation_time,
            'ready_at' => $this->ready_at?->toISOString(),
            'payment' => [
                'method' => $this->payment_method?->value,
                'method_label' => $this->payment_method?->getLabel(),
                'reference' => $this->payment_reference,
                'provider' => $this->payment_provider,
            ],
            'customer_note' => $this->customer_note,
            'branch' => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
