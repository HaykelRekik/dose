<?php

declare(strict_types=1);

namespace App\Http\Requests\API\Orders;

use App\Enums\PaymentMethod;
use App\Enums\ProductOptionGroupType;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    /**
     * Pre-fetched and validated product models to avoid re-querying in the service.
     */
    public ?Collection $hydratedProducts = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the initial validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('is_active', true)],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'payment_provider' => ['nullable', 'string', 'max:255'],
            'customer_note' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.selected_options' => ['present', 'array'], // `array` validates a JSON object {}
            'items.*.selected_options.*' => ['required', 'array'], // Each value must be an array
            'items.*.selected_options.*.*' => ['required', 'integer'], // Each item in the nested array is an option ID
        ];
    }

    /**
     * Get the custom validation messages for user-friendly errors.
     */
    public function messages(): array
    {
        return [
            'branch_id.where' => 'The selected branch is not accepting orders at the moment.',
            'items.*.product_id.exists' => 'One of the selected products is not available.',
            'items.*.selected_options.array' => 'The selected_options must be a valid object.',
            'items.*.selected_options.*.array' => 'Each option group must contain an array of selections.',
        ];
    }

    /**
     * Configure the validator instance for complex, cross-dependent validation.
     */
    public function withValidator(Validator $validator): void
    {
        if ($validator->fails()) {
            return;
        }

        $validator->after(function ($validator): void {
            $items = collect($this->input('items', []));
            $productIds = $items->pluck('product_id')->unique()->all();

            $products = Product::with('optionGroups.options')
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $this->hydratedProducts = $products;

            foreach ($items as $index => $item) {
                $product = $products->get($item['product_id']);
                if ( ! $product) {
                    continue;
                }

                $this->validateProductOptions($validator, $product, $item['selected_options'], $index);
            }
        });
    }

    /**
     * Perform deep validation on a single cart item's options using the new structure.
     */
    private function validateProductOptions(Validator $validator, Product $product, array $selectedOptions, int $itemIndex): void
    {
        $productOptionGroups = $product->optionGroups->keyBy('id');
        foreach ($productOptionGroups as $groupId => $group) {

            if ($group->is_required && empty($selectedOptions[$groupId])) {
                $validator->errors()->add(
                    "items.{$itemIndex}.selected_options",
                    "A selection for the required group '{$group->name}' is missing for product '{$product->name}'."
                );
            }
        }

        foreach ($selectedOptions as $submittedGroupId => $submittedOptionIds) {
            if ( ! $productOptionGroups->has($submittedGroupId)) {
                $validator->errors()->add(
                    "items.{$itemIndex}.selected_options",
                    "Invalid option group ID '{$submittedGroupId}' was submitted for product '{$product->name}'."
                );

                continue;
            }

            $group = $productOptionGroups->get($submittedGroupId);
            $validOptionsForGroup = $group->options->keyBy('id');

            if (ProductOptionGroupType::SINGLE_SELECT === $group->type && count($submittedOptionIds) > 1) {
                $validator->errors()->add(
                    "items.{$itemIndex}.selected_options.{$submittedGroupId}",
                    "Only one option can be selected for the group '{$group->name}'."
                );
            }

            foreach ($submittedOptionIds as $optionId) {
                if ( ! $validOptionsForGroup->has($optionId) || ! $validOptionsForGroup->get($optionId)->is_active) {
                    $validator->errors()->add(
                        "items.{$itemIndex}.selected_options.{$submittedGroupId}",
                        "Invalid or unavailable option ID '{$optionId}' was selected for group '{$group->name}'."
                    );
                }
            }
        }
    }
}
