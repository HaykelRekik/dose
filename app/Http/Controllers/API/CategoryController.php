<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Cache::flexible(
            key: 'categories',
            ttl: [
                5 * 60,
                30 * 60,
            ],
            callback: fn () => Category::query()
                ->active()
                ->orderBy('position')
                ->with(['products' => fn ($query) => $query->active()])
                ->withCount('products')
                ->get(),
        );

        return response()->success(
            message: 'Categories fetched successfully.',
            data: CategoryResource::collection($categories),
        );
    }

    public function show(Category $category)
    {
        $category = Cache::flexible(
            key: 'category:' . $category->id,
            ttl: [
                5 * 60,
                30 * 60,
            ],
            callback: fn () => $category->load(['products' => fn ($query) => $query->active()])
                ->loadCount(['products' => fn ($query) => $query->active()]),
        );

        return response()->success(
            message: 'Category fetched successfully.',
            data: CategoryResource::make($category)
        );
    }
}
