<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartLineRequest extends FormRequest
{
    public function rules()
    {
        $cartLine = $this->route('cartline');

        return [
            'type' => [
                'required',
                Rule::in(['quantity', 'remarks']),
            ],
            'quantity' => [
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    return $this->input('type') === 'quantity';
                }),
                'min:1',
                function ($attribute, $value, $fail) use ($cartLine) {

                    $purchasable = $cartLine->purchasable;

                    if ($value > $purchasable->stock) {
                        $fail('Quantity exceeds stock');

                        return;
                    }
                },
            ],
            'remarks' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) use ($cartLine) {
                    $purchasable = $cartLine->purchasable;

                    if ($purchasable instanceof Product) {
                        if ($value && ! $purchasable->allow_customer_remarks) {
                            $fail('You cant add remarks into this product.');
                        }
                    } elseif ($purchasable instanceof ProductVariant) {
                        $productVariant = ProductVariant::with('product')
                            ->where('id', $cartLine->purchasable_id)->first();

                        if ($value && ! $productVariant->product->allow_customer_remarks) {
                            $fail('You cant add remarks into this product.');
                        }
                    }
                },
            ],
            'media' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) use ($cartLine) {
                    $purchasable = $cartLine->purchasable;

                    if ($purchasable instanceof Product) {
                        if ($value && ! $purchasable->allow_customer_remarks) {
                            $fail('You cant add media remarks into this product.');
                        }
                    } elseif ($purchasable instanceof ProductVariant) {
                        $productVariant = ProductVariant::with('product')
                            ->where('id', $cartLine->purchasable_id)->first();

                        if ($value && ! $productVariant->product->allow_customer_remarks) {
                            $fail('You cant add media remarks into this product.');
                        }
                    }
                },
            ],
            'media.*' => 'url',
        ];
    }
}
