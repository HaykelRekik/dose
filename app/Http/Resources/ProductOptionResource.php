<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductOption */
class ProductOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'extra_price' => $this->extra_price,
        ];
    }
}
