<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $branches = Cache::flexible(
            key: 'branches',
            ttl: [
                5 * 60,
                30 * 60,
            ],
            callback: fn () => Branch::active()->get(),
        );

        return response()->success(
            message: 'Branches fetched successfully.',
            data: BranchResource::collection($branches),
        );
    }

    public function show(Branch $branch)
    {
        if ( ! $branch->is_active) {
            return response()->error(
                message: 'Branch not found.',
                status: HttpResponse::HTTP_NOT_FOUND,
            );
        }

        $branch = Cache::flexible(
            key: 'branch:' . $branch->id,
            ttl: [
                5 * 60,
                30 * 60,
            ],
            callback: fn () => $branch,
        );

        return response()->success(
            message: 'Branch fetched successfully.',
            data: BranchResource::make($branch),
        );
    }
}
