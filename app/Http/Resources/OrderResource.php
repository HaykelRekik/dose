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
            'status' => $this->status,
            'total_price' => $this->total_price,
            'estimated_preparation_time' => $this->estimated_preparation_time,
            'payment_method' => $this->payment_method,
            'customer_note' => $this->customer_note,
            'created_at' => $this->created_at->toIso8601String(),
            'branch' => BranchResource::make($this->whenLoaded('branch')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
