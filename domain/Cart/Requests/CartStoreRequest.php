<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CartStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // 'customer_id' => [
            //     'required',
            //     Rule::exists('customers', 'id'),
            // ],
            'purchasable_id' => [
                'required',
                Rule::exists('products', 'id'),
            ],
            'variant' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $purchasableId = $this->input('purchasable_id');

                    foreach ($value as $variantKey => $variantValue) {
                        $keyExists = ProductOption::whereProductId($purchasableId)->whereName($variantKey)->exists();

                        if ( ! $keyExists) {
                            $fail("Invalid $variantKey option.");

                            return;
                        }

                        $valueExists = ProductOptionValue::whereHas('productOption', function ($query) use ($purchasableId, $variantKey) {
                            $query->whereProductId($purchasableId)
                                ->whereName($variantKey);
                        })
                            ->whereName($variantValue)
                            ->exists();

                        if ( ! $valueExists) {
                            $fail("Invalid $variantValue option value.");

                            return;
                        }
                    }
                },
            ],
            'purchasable_type' => [
                'required',
                Rule::in(['Product', 'Service', 'Booking']),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $purchasableId = $this->input('purchasable_id');
                    $hasVariant = $this->input('variant');

                    if ($hasVariant) {
                        $product = ProductVariant::whereProductId($purchasableId)
                            ->whereJsonContains('combination', $hasVariant)
                            ->first();

                        if ( ! $product) {
                            $fail('Invalid product.');

                            return;
                        }

                        if ($value > $product->stock) {
                            $fail('The quantity exceeds the available quantity of the product.');

                            return;
                        }
                    }

                    $product = Product::find($purchasableId);

                    if ( ! $product) {
                        $fail('Invalid product.');

                        return;
                    }

                    if ($value > $product->stock) {
                        $fail('The quantity exceeds the available quantity of the product.');

                        return;
                    }
                },
            ],
            'meta' => [
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
