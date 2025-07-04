<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->active()
            ->orderBy('position')
            ->with(['products' => fn ($query) => $query->active()])
            ->withCount('products')
            ->get();

        return response()->success(
            message: 'Categories fetched successfully.',
            data: CategoryResource::collection($categories),
        );
    }

    public function show(Category $category)
    {
        $category
            ->load(['products' => fn ($query) => $query->active()])
            ->loadCount(['products' => fn ($query) => $query->active()]);

        return response()->success(
            message: 'Category fetched successfully.',
            data: CategoryResource::make($category)
        );
    }
}
