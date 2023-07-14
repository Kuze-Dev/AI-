<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCartLineRequest extends FormRequest
{
    public function rules()
    {
        return [
            'purchasable_id' => [
                'required',
                Rule::exists(Product::class, 'id'),
            ],
            'variant_id' => [
                'nullable',
                Rule::exists(ProductVariant::class, 'id')->where(function ($query) {
                    $purchasableId = $this->input('purchasable_id');
                    $purchasableType = $this->input('purchasable_type');

                    if ($purchasableType === 'Product') {
                        $query->where('product_id', $purchasableId);
                    }
                }),
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
                    $variantId = $this->input('variant_id');

                    if ( ! $purchasableId) {
                        $fail('Invalid product.');
                    }

                    if (!$variantId) {
                        $product = Product::find($purchasableId);

                        if (!$product) {
                            $fail('Invalid product.');

                            return;
                        }

                        if ($value > $product->stock) {
                            $fail('The quantity exceeds the available quantity of the product.');

                            return;
                        }
                    } else {
                        $productVariant = ProductVariant::find($variantId);

                        if (!$productVariant) {
                            $fail('Invalid productVariant.');

                            return;
                        }

                        if ($value > $productVariant->stock) {
                            $fail('The quantity exceeds the available quantity of the product.');

                            return;
                        }
                    }
                },
            ],
            'remarks' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $purchasableId = $this->input('purchasable_id');

                    $product = Product::find($purchasableId);

                    if ( ! $product) {
                        $fail('Invalid product.');

                        return;
                    }

                    if ($value && !$product->allow_customer_remarks) {
                        $fail('You cant add remarks into this product.');
                    }
                },
            ],
            'media' => [
                "nullable",
                "array",
                function ($attribute, $value, $fail) {
                    $purchasableId = $this->input('purchasable_id');

                    $product = Product::find($purchasableId);

                    if (!$product) {
                        $fail('Invalid product.');

                        return;
                    }

                    if ($value && !$product->allow_customer_remarks) {
                        $fail('You cant add media remarks into this product.');
                    }
                },
            ],
            'media.*' => 'url',
        ];
    }
}
