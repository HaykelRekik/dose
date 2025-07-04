<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ProductOptionGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductOptionGroup */
class ProductOptionGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_required' => $this->is_required,
            'type' => $this->type->value,

            'options' => ProductOptionResource::collection(
                $this->whenLoaded('options', fn () => $this->options->where('is_active', true)->values())
            ), ];
    }
}
