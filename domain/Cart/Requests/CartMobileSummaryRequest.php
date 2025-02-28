<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Helpers\PrivateCart\CartLineQuery;
use Domain\Cart\Models\CartLine;
use Domain\Discount\Models\Discount;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartMobileSummaryRequest extends FormRequest
{
    /** @var \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    private Collection $cartLinesCache;

    private array $cartLineIds;

    public function rules(): array
    {

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = customer_logged_in();

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
                            $query->whereBelongsTo(auth()->user());
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
            'billing_address_id' => [
                'nullable',
                Rule::exists(Address::class, (new Address())->getRouteKeyName())
                    ->where('customer_id', $customer->id),
            ],
            'shipping_method_id' => [
                'nullable',
                Rule::exists(ShippingMethod::class, (new ShippingMethod())->getRouteKeyName()),
            ],
            'shipping_address_id' => [
                'nullable',
                Rule::exists(Address::class, (new Address())->getRouteKeyName())
                    ->where('customer_id', $customer->id),
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

    /** @return \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    public function getCartLines(): Collection
    {
        if (empty($this->cartLinesCache)) {

            $this->cartLinesCache = app(CartLineQuery::class)->execute($this->cartLineIds);
        }

        return $this->cartLinesCache;
    }

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
