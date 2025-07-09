<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OrderItemOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderItemOption */
class OrderItemOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'option_name' => $this->whenLoaded('option', fn() => $this->option->name),
            'group_name' => $this->whenLoaded('optionGroup', fn() => $this->optionGroup->name),
            'extra_price' => $this->whenLoaded('option', fn() => $this->option->extra_price),
        ];
    }
}