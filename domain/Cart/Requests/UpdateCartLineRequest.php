<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Actions\CartPurchasableValidatorAction;
use Domain\Cart\Exceptions\InvalidPurchasableException;
use Domain\Cart\Helpers\ValidateRemarksMedia;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Throwable;

class UpdateCartLineRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var \Domain\Cart\Models\CartLine $cartLine */
        $cartLine = $this->route('cartline');

        return [
            'type' => [
                'required',
                Rule::in(['quantity', 'remarks']),
            ],
            'quantity' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => $this->input('type') === 'quantity'),
                'min:1',
                function ($attribute, $value, $fail) use ($cartLine) {

                    try {
                        /** @var \Domain\Product\Models\Product|\Domain\Product\Models\ProductVariant $purchasable */
                        $purchasable = $cartLine->purchasable;

                        app(CartPurchasableValidatorAction::class)
                            ->validatePurchasableUpdate($purchasable, $value);
                    } catch (Throwable $th) {
                        if ($th instanceof InvalidPurchasableException) {
                            $fail($th->getMessage());
                        }
                    }
                },
            ],
            'remarks' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) use ($cartLine) {

                    /** @var \Domain\Product\Models\Product|\Domain\Product\Models\ProductVariant $purchasable */
                    $purchasable = $cartLine->purchasable;

                    if ($purchasable instanceof Product) {
                        if ($value && ! $purchasable->allow_customer_remarks) {
                            $fail('You cant add remarks into this product.');
                        }
                    } elseif ($purchasable instanceof ProductVariant) {
                        /** @var \Domain\Product\Models\ProductVariant $productVariant */
                        $productVariant = ProductVariant::with('product')
                            ->where('id', $cartLine->purchasable_id)->first();

                        /** @var \Domain\Product\Models\Product $product */
                        $product = $productVariant->product;

                        if ($value && ! $product->allow_customer_remarks) {
                            $fail('You cant add remarks into this product.');
                        }
                    }
                },
            ],
            'remarks.notes' => [
                'nullable',
                'string',
                'max:255',
            ],
            'remarks.media' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) use ($cartLine) {
                    app(ValidateRemarksMedia::class)->execute($value, $fail);

                    /** @var \Domain\Product\Models\Product|\Domain\Product\Models\ProductVariant $purchasable */
                    $purchasable = $cartLine->purchasable;

                    if ($purchasable instanceof Product) {
                        if ($value && ! $purchasable->allow_customer_remarks) {
                            $fail('You cant add media remarks into this product.');
                        }
                    } elseif ($purchasable instanceof ProductVariant) {
                        /** @var \Domain\Product\Models\ProductVariant $productVariant */
                        $productVariant = ProductVariant::with('product')
                            ->where('id', $cartLine->purchasable_id)->first();

                        /** @var \Domain\Product\Models\Product $product */
                        $product = $productVariant->product;

                        if ($value && ! $product->allow_customer_remarks) {
                            $fail('You cant add media remarks into this product.');
                        }
                    }
                },
            ],
        ];
    }
}
