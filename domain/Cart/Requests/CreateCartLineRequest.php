<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCartLineRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'purchasable_id' => [
                'required',
                Rule::exists(Product::class, (new Product())->getRouteKeyName()),
            ],
            'variant_id' => [
                'nullable',
                Rule::exists(ProductVariant::class, (new ProductVariant())->getRouteKeyName()),
                function ($attribute, $value, $fail) {
                    $purchasableId = $this->input('purchasable_id');
                    $purchasableType = $this->input('purchasable_type');

                    if ($purchasableType === 'Product') {
                        ProductVariant::whereHas('product', function ($subQuery) use ($purchasableId) {
                            $subQuery->where((new Product())->getRouteKeyName(), $purchasableId);
                        });
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
                    $variantId = $this->input('variant_id') ?? null;

                    if (!$purchasableId) {
                        $fail('Invalid product.');
                    }

                    if (is_null($variantId)) {
                        $product = Product::where((new Product())->getRouteKeyName(), $purchasableId)->first();

                        if (!$product) {
                            $fail('Invalid product.');

                            return;
                        }

                        if ($value > $product->stock) {
                            $fail('The quantity exceeds the available quantity of the product.');

                            return;
                        }
                    } else {
                        $productVariant = ProductVariant::where(
                            (new ProductVariant())->getRouteKeyName(),
                            $variantId
                        )->whereHas('product', function ($query) use ($purchasableId) {
                            $query->where((new Product())->getRouteKeyName(), $purchasableId);
                        })->first();

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

                    $product = Product::where((new Product())->getRouteKeyName(), $purchasableId)->first();

                    if (!$product) {
                        $fail('Invalid product.');

                        return;
                    }

                    if ($value && !$product->allow_customer_remarks) {
                        $fail('You cant add remarks into this product.');
                    }
                },
            ],
            'remarks.notes' => [
                'nullable',
                'string',
                'max:255'
            ],
            'remarks.media' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $purchasableId = $this->input('purchasable_id');

                    $product = Product::where((new Product())->getRouteKeyName(), $purchasableId)->first();

                    if (!$product) {
                        $fail('Invalid product.');

                        return;
                    }

                    if ($value && !$product->allow_customer_remarks) {
                        $fail('You cant add media remarks into this product.');
                    }
                },
            ],
            'remarks.media.*' => 'url',
        ];
    }
}
