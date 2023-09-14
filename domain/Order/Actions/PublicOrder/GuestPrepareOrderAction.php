<?php

declare(strict_types=1);

namespace Domain\Order\Actions\PublicOrder;

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Models\Currency;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Product\Models\ProductVariant;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GuestPrepareOrderAction
{
    public function execute(GuestPlaceOrderData $guestPlaceOrderData): GuestPreparedOrderData
    {
        $customer = $guestPlaceOrderData->customer;

        $addresses = $this->prepareAddress($guestPlaceOrderData);

        $currency = $this->prepareCurrency();

        $cartLines = $this->prepareCartLines($guestPlaceOrderData);

        $taxZone = $this->prepareTax($guestPlaceOrderData);

        $discount = $this->prepareDiscount($guestPlaceOrderData);

        $paymentMethod = $this->preparePaymentMethod($guestPlaceOrderData);

        $shippingMethod = $this->prepareShippingMethod($guestPlaceOrderData);

        $notes = $guestPlaceOrderData->notes;

        $orderData = [
            'customer' => $customer,
            'shippingAddress' => $addresses['shippingAddress'],
            'billingAddress' => $addresses['billingAddress'],
            'currency' => $currency,
            'cartLine' => $cartLines,
            'notes' => $notes,
            'taxZone' => $taxZone,
            'discount' => $discount,
            'shippingMethod' => $shippingMethod,
            'paymentMethod' => $paymentMethod,
        ];

        return GuestPreparedOrderData::fromArray($orderData);
    }

    public function prepareAddress(GuestPlaceOrderData $guestPlaceOrderData): array
    {
        $shippingAddress = $guestPlaceOrderData->addresses->shipping;

        $billingAddress = $guestPlaceOrderData->addresses->billing;

        return [
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
        ];
    }

    public function prepareCurrency(): Currency
    {
        $currency = Currency::where('enabled', true)->first();

        if ( ! $currency instanceof Currency) {

            throw new BadRequestHttpException('No currency found');
        }

        return $currency;
    }

    /**
     * @param GuestPlaceOrderData $guestPlaceOrderData
     * @return Collection<int, CartLine>
     */
    public function prepareCartLines(GuestPlaceOrderData $guestPlaceOrderData): Collection
    {
        return CartLine::with(['purchasable' => function (MorphTo $query) {
            $query->morphWith([
                ProductVariant::class => ['product'],
            ]);
        }, ])
            ->whereCheckoutReference($guestPlaceOrderData->cart_reference)
            ->get();
    }

    public function prepareTax(GuestPlaceOrderData $guestPlaceOrderData): ?TaxZone
    {
        $countryId = $guestPlaceOrderData->addresses->billing->country_id;
        $stateId = $guestPlaceOrderData->addresses->billing->state_id;

        /** @var \Domain\Address\Models\State $state */
        $state = app(State::class)->where((new State())->getRouteKeyName(), $stateId)->first();

        /** @var \Domain\Address\Models\Country $country */
        $country = app(Country::class)->where((new Country())->getRouteKeyName(), $countryId)->first();

        $taxZone = Taxation::getTaxZone($country->id, $state->id);

        if ( ! $taxZone instanceof TaxZone) {
            return null;
        }

        return $taxZone;
    }

    public function prepareDiscount(GuestPlaceOrderData $guestPlaceOrderData): ?Discount
    {
        if ($guestPlaceOrderData->discountCode) {
            $discount = Discount::whereCode($guestPlaceOrderData->discountCode)
                ->whereStatus(DiscountStatus::ACTIVE)
                ->where(function ($query) {
                    $query->where('max_uses', '>', 0)
                        ->orWhereNull('max_uses');
                })
                ->where(function ($query) {
                    $query->where('valid_end_at', '>=', now())
                        ->orWhereNull('valid_end_at');
                })
                ->first();

            return $discount ?? null;
        }

        return null;
    }

    public function prepareShippingMethod(GuestPlaceOrderData $guestPlaceOrderData): ?ShippingMethod
    {
        return ShippingMethod::where((new ShippingMethod())->getRouteKeyName(), $guestPlaceOrderData->shipping_method)->first() ?? null;
    }

    public function preparePaymentMethod(GuestPlaceOrderData $guestPlaceOrderData): PaymentMethod
    {
        $paymentMethod = PaymentMethod::whereSlug($guestPlaceOrderData->payment_method)->first();

        if ( ! $paymentMethod instanceof PaymentMethod) {

            throw new BadRequestHttpException('No paymentMethod found');
        }

        return $paymentMethod;
    }
}
