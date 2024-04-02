<?php

declare(strict_types=1);

namespace Domain\Order\Requests\PublicOrder;

use App\HttpTenantApi\Requests\Auth\Address\AddressRequest;
use Domain\Cart\Actions\CartPurchasableValidatorAction;
use Domain\Cart\Enums\CartUserType;
use Domain\Cart\Exceptions\InvalidPurchasableException;
use Domain\Cart\Models\CartLine;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Validation\Rule;
use Throwable;

class GuestPlaceOrderRequest extends AddressRequest
{
    #[\Override]
    public function rules(): array
    {

        return [
            'customer.first_name' => 'required|string|max:255',
            'customer.last_name' => 'required|string|max:255',
            'customer.email' => [
                'required',
                Rule::email(),
                'max:255',
            ],
            'customer.mobile' => 'required|string|max:255',
            'addresses.billing' => [
                'required',
                parent::rules(),
            ],
            'addresses.shipping' => [
                'required',
                parent::rules(),
            ],
            'cart_reference' => [
                'required',
                function ($attribute, $value, $fail) {
                    $reference = $value;

                    $sessionId = $this->bearerToken();

                    $cartLines = CartLine::whereHas('cart', function ($query) use ($sessionId) {
                        $query->where('session_id', $sessionId);
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

                    $type = CartUserType::GUEST;
                    /** @var int|string $userId */
                    $userId = $sessionId;

                    //auth check
                    $checkAuth = app(CartPurchasableValidatorAction::class)->validateAuth($cartLineIds, $userId, $type);
                    if ($checkAuth !== count($cartLineIds)) {
                        $fail('Invalid cart line IDs.');
                    }

                    try {
                        //stock check
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
