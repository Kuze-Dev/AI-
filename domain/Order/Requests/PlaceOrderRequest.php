<?php

declare(strict_types=1);

namespace Domain\Order\Requests;

use Domain\Address\Models\Address;
use Domain\Cart\Actions\CartPurchasableValidatorAction;
use Domain\Cart\Enums\CartUserType;
use Domain\Cart\Exceptions\InvalidPurchasableException;
use Domain\Cart\Models\CartLine;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Throwable;

class PlaceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'addresses.shipping' => [
                'required',
                Rule::exists(Address::class, (new Address())->getRouteKeyName())->where(function ($query) {
                    /** @var \Domain\Customer\Models\Customer $customer */
                    $customer = auth()->user();

                    $query->where('customer_id', $customer->id);
                }),
            ],
            'addresses.billing' => [
                'required',
                Rule::exists(Address::class, (new Address())->getRouteKeyName())->where(function ($query) {

                    /** @var \Domain\Customer\Models\Customer $customer */
                    $customer = auth()->user();

                    $query->where('customer_id', $customer->id);
                }),
            ],
            'cart_reference' => [
                'required',
                function ($attribute, $value, $fail) {
                    $reference = $value;

                    /** @var \Domain\Customer\Models\Customer $customer */
                    $customer = auth()->user();

                    $cartLines = CartLine::whereHas('cart', function ($query) use ($customer) {
                        $query->whereBelongsTo($customer);
                    })
                        ->whereCheckoutReference($reference)
                        ->where('checkout_expiration', '>', now())
                        ->whereNull('checked_out_at')
                        ->count();

                    if (! $cartLines) {
                        $fail('No cart lines for checkout');

                        return;
                    }

                    $cartLines = CartLine::whereCheckoutReference($reference)->get();

                    $cartLineIds = array_values($cartLines->pluck('uuid')->toArray());

                    $type = CartUserType::AUTHENTICATED;

                    /** @var \Domain\Customer\Models\Customer $customer */
                    $customer = auth()->user();

                    /** @var int|string $userId */
                    $userId = $customer->id;

                    // auth check
                    $checkAuth = app(CartPurchasableValidatorAction::class)->validateAuth($cartLineIds, $userId, $type);
                    if ($checkAuth !== count($cartLineIds)) {
                        $fail('Invalid cart line IDs.');
                    }

                    try {
                        // stock check
                        $checkStocks = app(CartPurchasableValidatorAction::class)->validateCheckout($cartLineIds, $userId, $type);
                        if ($checkStocks !== count($cartLineIds)) {
                            $fail('Invalid stocks');
                        }
                    } catch (Throwable $th) {
                        if ($th instanceof InvalidPurchasableException) {
                            $fail($th->getMessage());
                        }
                    }
                },
            ],
            'notes' => [
                'nullable',
                'string',
                'min:1',
                'max:500',
            ],
            'discount_code' => [
                'nullable',
                'string',
                'min:1',
                'max:500',
            ],
            'payment_method' => [
                'required',
                Rule::exists(PaymentMethod::class, (new PaymentMethod())->getRouteKeyName()),
            ],
            'shipping_method' => [
                'required',
                Rule::exists(ShippingMethod::class, (new ShippingMethod())->getRouteKeyName()),
            ],
            'service_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (is_int($value) || is_string($value)) {
                        return true;
                    } else {
                        $fail($attribute.' is invalid.');
                    }
                },
            ],
        ];
    }
}
