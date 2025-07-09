<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Models\Product;
use Illuminate\Support\Collection;

readonly class PriceCalculationService
{
    public function calculateItemPrice(Product $product, Collection $selectedOptions, int $quantity = 1): array
    {
        $basePrice = $product->price;
        $optionsTotal = $this->calculateOptionsTotal($selectedOptions);
        $itemTotal = $basePrice + $optionsTotal;
        $totalPrice = $itemTotal * $quantity;

        return [
            'base_price' => $basePrice,
            'options_total' => $optionsTotal,
            'item_total' => $itemTotal,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'breakdown' => [
                'base_price' => $basePrice,
                'options' => $this->getOptionsBreakdown($selectedOptions),
                'subtotal' => $itemTotal,
                'quantity' => $quantity,
                'total' => $totalPrice,
            ],
        ];
    }

    public function calculateCartPrice(array $products): array
    {
        $productsWithOptions = $this->loadProductsWithOptions($products);

        $totalPrice = 0;
        $breakdown = [];

        foreach ($products as $productData) {
            $product = $productsWithOptions[$productData['product_id']];
            $selectedOptions = $this->getSelectedOptionsForProduct($product, $productData['options']);

            $itemPrice = $this->calculateItemPrice($product, $selectedOptions, $productData['quantity']);

            $totalPrice += $itemPrice['total_price'];
            $breakdown[] = $itemPrice['breakdown'];
        }

        return [
            'total_price' => $totalPrice,
            'breakdown' => $breakdown,
        ];
    }

    private function loadProductsWithOptions(array $products): Collection
    {
        $productIds = collect($products)->pluck('product_id')->unique();

        return Product::with([
            'optionGroups.options:id,product_option_group_id,name_en,name_ar,extra_price,is_active'
        ])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
    }

    private function getSelectedOptionsForProduct(Product $product, array $optionIds): Collection
    {
        $flattenedOptionIds = collect($optionIds)->flatten()->unique();

        return $product->optionGroups
            ->pluck('options')
            ->flatten()
            ->whereIn('id', $flattenedOptionIds)
            ->where('is_active', true);
    }

    private function calculateOptionsTotal(Collection $selectedOptions): float
    {
        return $selectedOptions->sum('extra_price');
    }

    private function getOptionsBreakdown(Collection $selectedOptions): array
    {
        return $selectedOptions->map(fn($option) => [
            'name' => $option->name,
            'price' => $option->extra_price,
        ])->toArray();
    }
}
