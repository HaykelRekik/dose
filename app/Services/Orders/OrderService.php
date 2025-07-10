<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

final readonly class OrderService
{
    public function __construct(
        private OrderNotificationService $notificationService
    ) {}

    /**
     * Creates a new order from validated data.
     * Products are fetched securely within the service to avoid security vulnerabilities.
     *
     * @param  array<string, mixed>  $data  The validated data from the StoreOrderRequest.
     *
     * @throws Throwable
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(callback: function () use ($data) {
            $products = $this->fetchValidatedProducts($data['items']);

            $hydratedCart = $this->buildHydratedCart($data['items'], $products);

            $totals = $this->calculateTotals($hydratedCart);

            $order = Order::create(attributes: [
                'user_id' => auth()->id(),
                'branch_id' => $data['branch_id'],
                'status' => OrderStatus::PENDING,
                'payment_method' => $data['payment_method'],
                'total_price' => $totals['totalPrice'],
                'estimated_preparation_time' => $totals['totalPrepTime'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_provider' => $data['payment_provider'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
            ]);

            $this->createOrderItems(order: $order, hydratedCart: $hydratedCart);

            $order->loadMissing(relations: ['items.options.option', 'items.options.optionGroup']);

            $order->update(attributes: ['order_snapshot' => $order->toArray()]);

            $this->notificationService->sendNewOrderNotification(branchId: $order->branch_id);

            return $order;
        });
    }

    /**
     * Fetch and validate products from the database based on cart items.
     * This ensures data integrity and prevents security vulnerabilities.
     *
     * @param  array<int, array<string, mixed>>  $cartItems
     * @return Collection<int, Product>
     *
     * @throws InvalidArgumentException
     */
    private function fetchValidatedProducts(array $cartItems): Collection
    {
        $productIds = collect($cartItems)->pluck('product_id')->unique()->all();

        $products = Product::with('optionGroups.options')
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        // Validate that all requested products exist and are active
        foreach ($productIds as $productId) {
            if ( ! $products->has($productId)) {
                throw new InvalidArgumentException(__('Product with ID :id is not available.', ['id' => $productId]));
            }
        }

        return $products;
    }

    /**
     * Combines validated request data with securely fetched Product models.
     *
     * @param  array<int, array<string, mixed>>  $cartItems
     * @param  Collection<int, Product>  $products
     * @return Collection<int, array<string, mixed>>
     */
    private function buildHydratedCart(array $cartItems, Collection $products): Collection
    {
        return collect($cartItems)->map(function (array $item) use ($products) {
            $product = $products->get($item['product_id']);
            $allOptionsForProduct = $product->optionGroups->flatMap->options;

            $selectedOptionIds = collect($item['selected_options'])->flatten()->all();

            $selectedOptions = $allOptionsForProduct->whereIn('id', $selectedOptionIds);

            return [
                'product' => $product,
                'quantity' => $item['quantity'],
                'selected_options' => $selectedOptions,
            ];
        });
    }

    /**
     * Calculates totals from the fully validated and hydrated cart data.
     *
     * @param  Collection<int, array<string, mixed>>  $hydratedCart
     * @return array{totalPrice: float, totalPrepTime: int}
     */
    private function calculateTotals(Collection $hydratedCart): array
    {
        return $hydratedCart->reduce(function ($carry, $item) {
            /** @var Product $product */
            $product = $item['product'];
            $optionsPrice = $item['selected_options']->sum('extra_price');
            $singleItemPrice = $product->price + $optionsPrice;

            $carry['totalPrice'] += $singleItemPrice * $item['quantity'];
            $carry['totalPrepTime'] += $product->estimated_preparation_time * $item['quantity'];

            return $carry;
        }, ['totalPrice' => 0.0, 'totalPrepTime' => 0]);
    }

    /**
     * Creates and persists OrderItem and OrderItemOption records.
     *
     * @param  Collection<int, array<string, mixed>>  $hydratedCart
     */
    private function createOrderItems(Order $order, Collection $hydratedCart): void
    {
        $orderItems = [];
        $orderItemOptions = [];

        foreach ($hydratedCart as $itemData) {
            /** @var Product $product */
            $product = $itemData['product'];
            /** @var Collection $selectedOptions */
            $selectedOptions = $itemData['selected_options'];

            $itemBasePrice = $product->price;
            $optionsPrice = $selectedOptions->sum('extra_price');

            $orderItems[] = [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_base_price' => $itemBasePrice,
                'quantity' => $itemData['quantity'],
                'item_total_price' => ($itemBasePrice + $optionsPrice) * $itemData['quantity'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        OrderItem::insert($orderItems);

        $createdItems = $order->items()->get();

        foreach ($hydratedCart as $index => $itemData) {
            /** @var Collection $selectedOptions */
            $selectedOptions = $itemData['selected_options'];

            if ($selectedOptions->isNotEmpty()) {
                $currentItem = $createdItems[$index];
                foreach ($selectedOptions as $option) {
                    $orderItemOptions[] = [
                        'order_item_id' => $currentItem->id,
                        'product_option_group_id' => $option->product_option_group_id,
                        'product_option_id' => $option->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if ( ! empty($orderItemOptions)) {
            OrderItemOption::insert($orderItemOptions);
        }
    }
}
