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
use Throwable;

final class OrderService
{
    /**
     * Creates a new order from validated data and pre-fetched product models.
     *
     * @param  array<string, mixed>  $data  The validated data from the StoreOrderRequest.
     * @param  Collection  $products  The hydrated product models from the FormRequest.
     *
     * @throws Throwable
     */
    public function createOrder(array $data, Collection $products): Order
    {
        return DB::transaction(function () use ($data, $products) {
            $hydratedCart = $this->buildHydratedCart($data['items'], $products);

            $totals = $this->calculateTotals($hydratedCart);

            $order = Order::create([
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

            $this->createOrderItems($order, $hydratedCart);

            $order->load('items.options.option', 'items.options.optionGroup');

            $order->update(['order_snapshot' => $order->toArray()]);

            return $order;
        });
    }

    /**
     * Combines validated request data with pre-fetched Product models.
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
