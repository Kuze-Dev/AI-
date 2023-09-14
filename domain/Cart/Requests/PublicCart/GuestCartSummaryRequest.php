<?php

declare(strict_types=1);

namespace Domain\Cart\Requests\PublicCart;

use App\HttpTenantApi\Requests\Auth\Address\AddressRequest;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Models\CartLine;
use Domain\Discount\Models\Discount;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Collection;

class GuestCartSummaryRequest extends AddressRequest
{
    /** @var \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    private Collection $cartLinesCache;

    public function rules(): array
    {
        return [
            'cart_line_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {

                    $cartLineIds = $value;

                    $cartLines = $this->getCartLines();

                    if (count($cartLineIds) !== $cartLines->count()) {
                        $fail('Invalid cart line IDs.');
                    }
                },
            ],
            'billing_address' => [
                'required',
                parent::rules(),
            ],
            'shipping_address' => [
                'required',
                parent::rules(),
            ],
            'shipping_method_id' => [
                'nullable',
                Rule::exists(ShippingMethod::class, (new ShippingMethod())->getRouteKeyName()),
            ],
            'service_id' => [
                'nullable',
                'int',
            ],
            'discount_code' => [
                'nullable',
            ],
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    public function getCartLines(): Collection
    {
        if (empty($this->cartLinesCache)) {
            $cartLineIds = $this->validated('cart_line_ids');

            $this->cartLinesCache = CartLine::query()
                ->with('purchasable')
                ->whereHas('cart', function ($query) {
                    $query->where('session_id', $this->bearerToken());
                })
                ->whereNull('checked_out_at')
                ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
                ->get();
        }

        return $this->cartLinesCache;
    }

    /** @return \Domain\Address\Models\Country|null */
    public function getCountry(): ?Country
    {
        if ($id = $this->validated('billing_address')['country_id']) {
            /** @var \Domain\Address\Models\Country $country */
            $country = app(Country::class)->where((new Country())->getRouteKeyName(), $id)->first();

            return $country;
        }

        return null;
    }

    public function getState(): ?State
    {
        if ($id = $this->validated('billing_address')['state_id']) {

            /** @var \Domain\Address\Models\State $state */
            $state = app(State::class)->where((new State())->getRouteKeyName(), $id)->first();

            return $state;
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

    // public function getShippingAddress(): ?Address
    // {
    //     if ($id = $this->validated('shipping_address_id')) {
    //         return app(Address::class)->where((new Address())->getRouteKeyName(), $id)->first();
    //     }

    //     return null;
    // }

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
