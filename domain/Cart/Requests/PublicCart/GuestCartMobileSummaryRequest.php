<?php

declare(strict_types=1);

namespace Domain\Cart\Requests\PublicCart;

use App\HttpTenantApi\Requests\Auth\Address\AddressRequest;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Helpers\PrivateCart\CartLineQuery;
use Domain\Cart\Models\CartLine;
use Domain\Discount\Models\Discount;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\Rule;

class GuestCartMobileSummaryRequest extends AddressRequest
{
    /** @var \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    private Collection $cartLinesCache;

    private array $cartLineIds;

    #[\Override]
    public function rules(): array
    {
        return [
            'reference' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $cartLines = CartLine::with(['purchasable' => function (MorphTo $query) {
                        $query->morphWith([
                            Product::class => ['media'],
                            ProductVariant::class => ['product.media'],
                        ]);
                    }, 'media'])
                        ->whereHas('cart', function ($query) {
                            $query->where('session_id', $this->bearerToken());
                        })
                        ->whereCheckoutReference($value);

                    if ($cartLines->count() === 0) {
                        $fail('Invalid reference');
                    }

                    $cartLineIds = $cartLines->pluck('uuid')->toArray();

                    $this->cartLineIds = $cartLineIds;

                    $cartLines = $this->getCartLines();

                    if (count($cartLineIds) !== $cartLines->count()) {
                        $fail('Invalid cart line IDs.');
                    }
                },
            ],
            'customer' => 'array|nullable',
            'customer.first_name' => 'nullable|string|max:255',
            'customer.last_name' => 'nullable|string|max:255',
            'customer.email' => [
                'nullable',
                Rule::email(),
                'max:255',
            ],
            'customer.mobile' => 'nullable|string|max:255',
            'customer.tier_id' => 'nullable|int',
            'billing_address' => [
                'nullable',
                parent::rules(),
            ],
            'shipping_address' => [
                'nullable',
                parent::rules(),
            ],
            'shipping_method_id' => [
                'nullable',
                Rule::exists(ShippingMethod::class, (new ShippingMethod)->getRouteKeyName()),
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
            'discount_code' => [
                'nullable',
            ],
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    public function getCartLines(): Collection
    {
        if (empty($this->cartLinesCache)) {

            /** @var string $sessionId */
            $sessionId = $this->bearerToken();

            $this->cartLinesCache = app(CartLineQuery::class)->guests($this->cartLineIds, $sessionId);
        }

        return $this->cartLinesCache;
    }

    public function getCountry(): ?Country
    {
        if ($this->validated('billing_address')) {
            $id = $this->validated('billing_address')['country_id'];

            /** @var \Domain\Address\Models\Country $country */
            $country = app(Country::class)->where((new Country)->getRouteKeyName(), $id)->first();

            return $country;
        }

        return null;
    }

    public function getState(): ?State
    {
        if ($this->validated('billing_address')) {
            $id = $this->validated('billing_address')['state_id'];
            /** @var \Domain\Address\Models\State $state */
            $state = app(State::class)->where((new State)->getRouteKeyName(), $id)->first();

            return $state;
        }

        return null;
    }

    public function getShippingAddress(): ?ShippingAddressData
    {
        if ($shippingAddressData = $this->validated('shipping_address')) {

            $stateId = $this->validated('shipping_address')['state_id'];
            $countryId = $this->validated('shipping_address')['country_id'];

            /** @var \Domain\Address\Models\State $state */
            $state = app(State::class)->where((new State)->getRouteKeyName(), $stateId)->first();

            /** @var \Domain\Address\Models\Country $country */
            $country = app(Country::class)->where((new Country)->getRouteKeyName(), $countryId)->first();

            return new ShippingAddressData(
                address: $shippingAddressData['address_line_1'],
                city: $shippingAddressData['city'],
                zipcode: $shippingAddressData['zip_code'],
                code: $state->code,
                state: $state,
                country: $country,
            );
        }

        return null;
    }

    public function toRecieverDTO(): ?ReceiverData
    {
        if ($customerData = $this->validated('customer')) {
            return ReceiverData::fromArray($customerData);
        }

        return null;
    }

    public function getShippingMethod(): ?ShippingMethod
    {
        if ($id = $this->validated('shipping_method_id')) {
            return app(ShippingMethod::class)->where((new ShippingMethod)->getRouteKeyName(), $id)->first();
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
