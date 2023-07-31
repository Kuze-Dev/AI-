<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Models\CartLine;
use Domain\Discount\Models\Discount;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartSummaryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cart_line_ids' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {

                    $cartLineIds = explode(',', $value);

                    $cartLines = CartLine::whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
                        ->whereHas('cart', function ($query) {
                            $query->whereBelongsTo(auth()->user());
                        })
                        ->whereNull('checked_out_at')
                        ->get();

                    if (count($cartLineIds) !== $cartLines->count()) {
                        $fail('Invalid cart line IDs.');
                    }
                },
            ],
            'billing_address_id' => [
                'nullable',
                Rule::exists(Address::class, (new Address())->getRouteKeyName())
                    /** @phpstan-ignore-next-line */
                    ->where('customer_id', auth()->user()->id),
            ],
            'shipping_method_id' => [
                'nullable',
                Rule::exists(ShippingMethod::class, (new ShippingMethod())->getRouteKeyName()),
            ],
            'shipping_address_id' => [
                'nullable',
                Rule::exists(Address::class, (new Address())->getRouteKeyName())
                    /** @phpstan-ignore-next-line */
                    ->where('customer_id', auth()->user()->id),
            ],
            'service_id' => [
                'nullable',
                'int',
            ],
            'discount_code' => [
                'nullable',
                // Rule::exists(Discount::class, (new Discount())->getRouteKeyName()),

            ],
        ];
    }

    /** @return \Domain\Address\Models\Country|null */
    public function getCountry(): ?Country
    {
        if ($id = $this->validated('billing_address_id')) {
            /** @var \Domain\Address\Models\Address $billingAddress */
            $billingAddress = Address::with('state.country')
                ->where((new Address())->getRouteKeyName(), $id)->first();

            /** @var \Domain\Address\Models\State $state */
            $state = $billingAddress->state;

            /** @var \Domain\Address\Models\Country $country */
            $country = $state->country;

            return $country;
        }

        return null;
    }

    public function getShippingMethod(): ?ShippingMethod
    {
        if ($id = $this->validated('shipping_method_id')) {
            return app(ShippingMethod::class)->where((new ShippingMethod())->getRouteKeyName(), $id)->first();
        }

        return null;
    }

    public function getShippingAddress(): ?Address
    {
        if ($id = $this->validated('shipping_address_id')) {
            return app(Address::class)->where((new Address())->getRouteKeyName(), $id)->first();
        }

        return null;
    }

    public function getState(): ?State
    {
        if ($id = $this->validated('billing_address_id')) {
            /** @var \Domain\Address\Models\Address $billingAddress */
            $billingAddress = Address::with('state')
                ->where((new Address())->getRouteKeyName(), $id)->first();

            /** @var \Domain\Address\Models\State $state */
            $state = $billingAddress->state;

            return $state;
        }

        return null;
    }

    public function getDiscount(): ?Discount
    {
        $id = $this->validated('discount_code');

        if ($id) {
            $discount = app(Discount::class)
                ->with([
                    'discountCondition',
                    'discountRequirement',
                    'discountLimits',
                ])
                ->where('code', $id)
                ->first();

            return $discount;
        }

        return null;
    }
}
