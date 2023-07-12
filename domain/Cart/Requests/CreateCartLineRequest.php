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
                Rule::exists(ProductVariant::class, 'id'),
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

                    if ( ! $purchasableId) {
                        $fail('Invalid product.');
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
}
