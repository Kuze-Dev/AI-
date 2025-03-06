<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Actions\CartPurchasableValidatorAction;
use Domain\Cart\Enums\CartUserType;
use Domain\Cart\Exceptions\InvalidPurchasableException;
use Domain\Cart\Helpers\ValidateRemarksMedia;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Throwable;

class CreateCartLineRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'purchasable_id' => [
                'required',
                Rule::exists(Product::class, (new Product)->getRouteKeyName()),
            ],
            'variant_id' => [
                'nullable',
                Rule::exists(ProductVariant::class, (new ProductVariant)->getRouteKeyName()),
                function ($attribute, $value, $fail) {
                    $purchasableId = $this->input('purchasable_id');
                    $purchasableType = $this->input('purchasable_type');

                    if ($purchasableType === 'Product') {
                        ProductVariant::whereHas('product', function ($subQuery) use ($purchasableId) {
                            $subQuery->where((new Product)->getRouteKeyName(), $purchasableId);
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

                    if (! $purchasableId) {
                        $fail('Invalid product.');
                    }

                    $type = guest_customer_logged_in() ? CartUserType::AUTHENTICATED : CartUserType::GUEST;
                    /** @var int|string $userId */
                    $userId = guest_customer_logged_in() ? customer_logged_in()->id : $this->bearerToken();

                    if (is_null($variantId)) {
                        try {
                            app(CartPurchasableValidatorAction::class)->validateProduct($purchasableId, $value, $userId, $type);
                        } catch (Throwable $th) {
                            if ($th instanceof InvalidPurchasableException) {
                                $fail($th->getMessage());
                            }
                        }
                    } else {
                        try {
                            app(CartPurchasableValidatorAction::class)->validateProductVariant(
                                $purchasableId,
                                $variantId,
                                $value,
                                $userId,
                                $type
                            );
                        } catch (Throwable $th) {
                            if ($th instanceof InvalidPurchasableException) {
                                $fail($th->getMessage());
                            }
                        }
                    }
                },
            ],
            'remarks' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $purchasableId = $this->input('purchasable_id');

                    $product = Product::where((new Product)->getRouteKeyName(), $purchasableId)->first();

                    if (! $product) {
                        $fail('Invalid product.');

                        return;
                    }

                    if ($value && ! $product->allow_customer_remarks) {
                        $fail('You cant add remarks into this product.');
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
                function ($attribute, $value, $fail) {
                    app(ValidateRemarksMedia::class)->execute($value, $fail);

                    $purchasableId = $this->input('purchasable_id');

                    $product = Product::where((new Product)->getRouteKeyName(), $purchasableId)->first();

                    if (! $product) {
                        $fail('Invalid product.');

                        return;
                    }

                    if ($value && ! $product->allow_customer_remarks) {
                        $fail('You cant add media remarks into this product.');
                    }
                },
            ],
        ];
    }
}
