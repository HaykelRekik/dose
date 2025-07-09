<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Orders\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
        Auth::loginUsingId(2);

    }

    public function index(Request $request): JsonResponse
    {
        Auth::loginUsingId(2);
        $orders = $request->user()->orders()->latest()->paginate(10);

        return response()->success(
            message: 'Orders retrieved successfully.',
            data: OrderResource::collection($orders),
        );
    }

    public function show(Order $order): JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            return response()->error(
                message: 'Unauthorized.',
                status: Response::HTTP_FORBIDDEN,
            );
        }

        $order->loadMissing('branch', 'items.options.option', 'items.options.optionGroup');

        return response()->success(
            message: 'Order retrieved successfully.',
            data: OrderResource::make($order),
        );
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $hydratedProducts = $request->hydratedProducts;

            $order = $this->orderService->createOrder(
                data: $validatedData,
                products: $hydratedProducts,
            );

            $order->loadMissing('branch', 'items.options.option', 'items.options.optionGroup');

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
