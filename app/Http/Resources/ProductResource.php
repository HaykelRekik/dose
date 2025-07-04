<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'estimated_preparation_time' => $this->estimated_preparation_time,
            'image_path' => $this->image_path,

            'categories' => CategoryResource::collection($this->whenLoaded('categories')),

            'option_groups' => ProductOptionGroupResource::collection($this->whenLoaded('optionGroups')),
        ];
    }
}
