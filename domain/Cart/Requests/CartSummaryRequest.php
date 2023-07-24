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
    public function rules()
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
            'country_id' => [
                'required',
                Rule::exists(Country::class, (new Country())->getRouteKeyName()),
            ],
            'shipping_method_id' => [
                'nullable',
                Rule::exists(ShippingMethod::class, (new ShippingMethod())->getRouteKeyName()),
            ],
            'address_id' => [
                'nullable',
                Rule::exists(Address::class, (new Address())->getRouteKeyName()),
            ],
            'state_id' => [
                'nullable',
                Rule::exists(State::class, (new State())->getRouteKeyName()),
            ],
            'discount_code' => [
                'nullable',
                Rule::exists(Discount::class, (new Discount())->getRouteKeyName()),
            ],
        ];
    }

    public function getCountry(): ?Country
    {
        if ($id = $this->validated('country_id')) {
            return app(Country::class)->resolveRouteBinding($id);
        }

        return null;
    }

    public function getShippingMethod(): ?ShippingMethod
    {
        if ($id = $this->validated('shipping_method_id')) {
            return app(ShippingMethod::class)->resolveRouteBinding($id);
        }

        return null;
    }

    public function getShippingAddress(): ?Address
    {
        if ($id = $this->validated('address_id')) {
            return app(Address::class)->resolveRouteBinding($id);
        }

        return null;
    }

    public function getState(): ?State
    {
        if ($id = $this->validated('state_id')) {
            return app(State::class)->resolveRouteBinding($id);
        }

        return null;
    }

    public function getDiscount(): ?Discount
    {
        if ($id = $this->validated('discount_code')) {
            return app(Discount::class)->resolveRouteBinding($id);
        }

        return null;
    }
}
