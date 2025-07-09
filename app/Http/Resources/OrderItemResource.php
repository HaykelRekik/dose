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
            'quantity' => $this->quantity,
            'base_price' => $this->product_base_price,
            'total_price' => $this->item_total_price,
//            'options' => OrderItemOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
