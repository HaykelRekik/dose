<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Orders\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\Orders\OrderService;
use Illuminate\Http\JsonResponse;
use Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $hydratedProducts = $request->hydratedProducts;

            $order = $this->orderService->createOrder(
                data: $validatedData,
                products: $hydratedProducts,
            );

            $order->loadMissing('branch', 'items.options');

            return response()->success(
                message: 'Order created successfully.',
                data: OrderResource::make($order),
                status: Response::HTTP_CREATED,
            );
        } catch (Throwable $e) {
            Log::error('Order creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);


            return response()->error(
                message: 'An unexpected error occurred. Please try again later.',
                status: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
