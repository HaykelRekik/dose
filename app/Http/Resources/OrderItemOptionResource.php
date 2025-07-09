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
            'group' => [
                'id' => $this->product_option_group_id,
                'name' => $this->group_name,
                'type' => $this->group_type,
                'is_required' => $this->group_is_required,
            ],
            'option' => [
                'id' => $this->product_option_id,
                'name' => $this->option_name,
                'description' => $this->option_description,
                'extra_price' => $this->option_extra_price,
            ],
        ];
    }
}
