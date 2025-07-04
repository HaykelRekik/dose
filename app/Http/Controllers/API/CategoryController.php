<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->active()
            ->orderBy('position')
            ->withCount('products')
            ->get();

        return response()->success(
            message: 'Categories fetched successfully.',
            data: CategoryResource::collection($categories),
        );
    }

    public function show(Category $category)
    {
        return response()->success(
            message: 'Category fetched successfully.',
            data: CategoryResource::make($category->loadCount('products'))
        );
    }
}
