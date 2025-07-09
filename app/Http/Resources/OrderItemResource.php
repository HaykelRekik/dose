<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderItem */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product_id,
                'name' => $this->product_name,
                'base_price' => $this->product_base_price,
                'preparation_time' => $this->product_preparation_time,
            ],
            'quantity' => $this->quantity,
            'item_total_price' => $this->item_total_price,
            'options' => OrderItemOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
