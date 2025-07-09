<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Orders;

use App\Enums\PaymentMethod;
use App\Enums\ProductOptionGroupType;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'products.*.options' => ['required', 'array'],
            'products.*.options.*' => ['required', 'array'],
            'products.*.options.*.*' => ['required', 'integer', 'exists:product_options,id'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'payment_provider' => ['nullable', 'string', 'max:100'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->filled('products')) {
                foreach ($this->input('products') as $index => $productData) {
                    $this->validateProductOptions($validator, $productData, $index);
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Please select a branch.',
            'branch_id.exists' => 'The selected branch does not exist.',
            'products.required' => 'Please add at least one product to your order.',
            'products.array' => 'The products must be an array.',
            'products.min' => 'Please add at least one product to your order.',
            'products.*.product_id.required' => 'Please select a product.',
            'products.*.product_id.exists' => 'The selected product does not exist.',
            'products.*.quantity.required' => 'Please specify the quantity for each product.',
            'products.*.quantity.integer' => 'The quantity must be an integer.',
            'products.*.quantity.min' => 'The quantity for each product must be at least 1.',
            'products.*.quantity.max' => 'The quantity for each product cannot exceed 10.',
            'products.*.options.required' => 'Please select options for each product.',
            'products.*.options.array' => 'Options must be provided as an array for each product.',
            'payment_method.required' => 'Please select a payment method.',
            'customer_note.max' => 'Customer note cannot exceed 1000 characters.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    private function validateProductOptions($validator, array $productData, int $index): void
    {
        $product = Product::with([
            'optionGroups' => fn ($query) => $query->select('id', 'product_id', 'name', 'type', 'is_required'),
            'optionGroups.options' => fn ($query) => $query->select('id', 'product_option_group_id', 'name', 'is_active')
                ->where('is_active', true),
        ])->find($productData['product_id']);

        if ( ! $product?->is_active) {
            $validator->errors()->add("products.{$index}.product_id", 'The selected product is not available.');

            return;
        }

        $selectedOptions = collect($productData['options'] ?? []);
        $productGroupIds = $product->optionGroups->pluck('id');

        $this->validateGroupsBelongToProduct($validator, $selectedOptions, $productGroupIds, $index);
        $this->validateGroupRequirements($validator, $product->optionGroups, $selectedOptions, $index);
    }

    private function validateGroupsBelongToProduct($validator, Collection $selectedOptions, Collection $productGroupIds, int $index): void
    {
        $selectedOptions->keys()
            ->reject(fn ($groupId) => $productGroupIds->contains($groupId))
            ->each(fn ($groupId) => $validator->errors()->add(
                "products.{$index}.options.{$groupId}",
                'The selected option group does not belong to this product.'
            ));
    }

    private function validateGroupRequirements($validator, Collection $groups, Collection $selectedOptions, int $index): void
    {
        $groups->each(function ($group) use ($validator, $selectedOptions, $index): void {
            $groupId = $group->id;
            $selectedOptionIds = collect($selectedOptions[$groupId] ?? []);

            // Check required groups

            if ($group->is_required && $selectedOptionIds->isEmpty()) {
                $validator->errors()->add(
                    "products.{$index}.options.{$groupId}",
                    "The {$group->name} option group is required."
                );

                return;
            }

            if ($selectedOptionIds->isNotEmpty()) {
                $this->validateGroupConstraints($validator, $group, $selectedOptionIds, $index);
                $this->validateOptionsBelongToGroup($validator, $group, $selectedOptionIds, $index);
            }
        });
    }

    private function validateGroupConstraints($validator, $group, Collection $selectedOptionIds, int $index): void
    {
        if (ProductOptionGroupType::SINGLE_SELECT === $group->type && $selectedOptionIds->count() > 1) {
            $validator->errors()->add(
                "products.{$index}.options.{$group->id}",
                "The {$group->name} option group allows only one selection."
            );
        }
    }

    private function validateOptionsBelongToGroup($validator, $group, Collection $selectedOptionIds, int $index): void
    {
        $validOptionIds = $group->options->pluck('id');
        $invalidOptions = $selectedOptionIds->diff($validOptionIds);

        if ($invalidOptions->isNotEmpty()) {
            $validator->errors()->add(
                "products.{$index}.options.{$group->id}",
                'Some selected options are not available for this group.'
            );
        }
    }
}
