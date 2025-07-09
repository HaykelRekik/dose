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
            'group_name' => $this->group_name,
            'option_name' => $this->option_name,
            'extra_price' => $this->option_extra_price,
        ];
    }
}
