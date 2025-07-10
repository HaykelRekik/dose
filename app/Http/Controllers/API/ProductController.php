<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->active()
            ->when(
                request()->filled('category'),
                fn (Builder $query) => $query->whereRelation('categories', 'slug', request('category')),
            )
            ->get();

        return response()->success(
            message: 'Products fetched successfully.',
            data: ProductResource::collection($products),
        );
    }

    public function show(Product $product): JsonResponse
    {
        if ( ! $product->is_active) {
            return response()->error(
                message: 'Product not found.',
                status: HttpResponse::HTTP_NOT_FOUND,
            );
        }

        $product = Cache::flexible(
            key: 'product:' . $product->id,
            ttl: [
                5 * 60,
                30 * 60,
            ],
            callback: fn () => $product->load('categories', 'optionGroups.options'),
        );

        return response()->success(
            message: 'Product fetched successfully.',
            data: ProductResource::make($product),
        );
    }
}
