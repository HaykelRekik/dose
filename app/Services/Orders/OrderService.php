<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

readonly class OrderService
{
    public function __construct(
        private PriceCalculationService $priceCalculator
    ) {}

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cartPrice = $this->priceCalculator->calculateCartPrice($data['products']);

            $order = Order::create([
                'user_id' => auth()->id(),
                'branch_id' => $data['branch_id'],
                'status' => OrderStatus::PENDING,
                'total_price' => $cartPrice['total_price'],
                'estimated_preparation_time' => $this->calculateTotalPreparationTime($data['products']),
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_provider' => $data['payment_provider'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'products_snapshot' => $this->createProductsSnapshot($data['products']),
            ]);

            $this->createOrderItems($order, $data['products']);

            return $order->load(['items.options', 'branch']);
        });
    }

    private function createOrderItems(Order $order, array $products): void
    {
        foreach ($products as $productData) {
            $product = $this->getProductWithOptions($productData['product_id']);
            $selectedOptions = $this->getSelectedOptions($productData['options']);
            $priceData = $this->priceCalculator->calculateItemPrice($product, $selectedOptions, $productData['quantity']);

            $orderItem = $this->createOrderItem($order, $product, $priceData, $productData['quantity']);

            $this->createOrderItemOptions($orderItem, $selectedOptions, $productData['options']);
        }
    }

    private function calculateTotalPreparationTime(array $products): int
    {
        $totalTime = 0;

        foreach ($products as $productData) {
            $product = Product::find($productData['product_id']);
            $totalTime += $product->estimated_preparation_time * $productData['quantity'];
        }

        return $totalTime;
    }

    private function createProductsSnapshot(array $products): array
    {
        $snapshot = [];

        foreach ($products as $productData) {
            $product = Product::find($productData['product_id']);
            $snapshot[] = $this->createProductSnapshot($product);
        }

        return $snapshot;
    }

    private function getProductWithOptions(int $productId): Product
    {
        return Product::with([
            'optionGroups:id,product_id,name_en,name_ar,type,is_required',
            'optionGroups.options:id,product_option_group_id,name_en,name_ar,extra_price,is_active',
        ])->findOrFail($productId);
    }

    private function getSelectedOptions(array $optionData): Collection
    {
        $optionIds = collect($optionData)->flatten()->unique();

        return ProductOption::with([
            'optionGroup:id,name_en,name_ar,type,is_required',
        ])
            ->whereIn('id', $optionIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
    }

    private function createProductSnapshot(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'estimated_preparation_time' => $product->estimated_preparation_time,
            'category' => $product->category?->name,
            'is_active' => $product->is_active,
            'created_at' => $product->created_at,
            'snapshot_taken_at' => now(),
        ];
    }

    private function createOrderItem(Order $order, Product $product, array $priceData, int $quantity): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_base_price' => $product->price,
            'product_preparation_time' => $product->estimated_preparation_time,
            'product_snapshot' => $this->createProductSnapshot($product),
            'quantity' => $quantity,
            'item_total_price' => $priceData['total_price'],
        ]);
    }

    private function createOrderItemOptions(OrderItem $orderItem, Collection $selectedOptions, array $optionData): void
    {
        $optionInserts = collect($optionData)->flatMap(function ($optionIds, $groupId) use ($orderItem, $selectedOptions) {
            return collect($optionIds)->map(function ($optionId) use ($orderItem, $selectedOptions, $groupId) {
                $option = $selectedOptions->get($optionId);

                if ( ! $option) {
                    return null;
                }

                return [
                    'order_item_id' => $orderItem->id,
                    'product_option_group_id' => $groupId,
                    'product_option_id' => $optionId,
                    'group_name' => $option->optionGroup->name,
                    'group_type' => $option->optionGroup->type,
                    'group_is_required' => $option->optionGroup->is_required,
                    'option_name' => $option->name,
                    'option_description' => $option->description,
                    'option_extra_price' => $option->extra_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
        })->filter()->toArray();

        if ( ! empty($optionInserts)) {
            OrderItemOption::insert($optionInserts);
        }
    }
}
