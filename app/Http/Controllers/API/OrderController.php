<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Orders\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\Orders\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());

            return response()->success(
                message: __('Order created successfully.'),
                data: new OrderResource($order),
                status: Response::HTTP_CREATED
            );

        } catch (Exception $e) {
            return response()->error(
                message: __('Failed to create order. Please try again.'),
                status: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $order = auth()->user()
                ->orders()
                ->with(['items.options', 'branch'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new OrderResource($order),
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
