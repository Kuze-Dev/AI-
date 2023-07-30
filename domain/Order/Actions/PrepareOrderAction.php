<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Models\Currency;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Product\Models\ProductVariant;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Log;

class PrepareOrderAction
{
    public function execute(PlaceOrderData $placeOrderData): PreparedOrderData
    {
        $customer = auth()->user();

        $addresses = $this->prepareAddress($placeOrderData);

        $currency = $this->prepareCurrency();

        $cartLines = $this->prepareCartLines($placeOrderData);

        $taxZone = $this->prepareTax($placeOrderData);

        $discount = $this->prepareDiscount($placeOrderData);

        $paymentMethod = $this->preparePaymentMethod($placeOrderData);

        $shippingMethod = $this->prepareShippingMethod($placeOrderData);

        $notes = $placeOrderData->notes;

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

        return PreparedOrderData::fromArray($orderData);
    }

    private function prepareAddress(PlaceOrderData $placeOrderData): array
    {
        $shippingAddress = Address::with('state.country')->find($placeOrderData->addresses->shipping);

        $billingAddress = Address::with('state.country')->find($placeOrderData->addresses->billing);

        return [
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
        ];
    }

    private function prepareCurrency(): Currency
    {
        $currency = Currency::where('default', true)->first();

        if (!$currency instanceof Currency) {

            throw new BadRequestHttpException('No currency found');
        }

        return $currency;
    }

    /**
     * @param PlaceOrderData $placeOrderData
     * @return Collection<int, CartLine>
     */
    private function prepareCartLines(PlaceOrderData $placeOrderData): Collection
    {
        return CartLine::with(['purchasable' => function (MorphTo $query) {
            $query->morphWith([
                ProductVariant::class => ['product'],
            ]);
        },])
            ->whereCheckoutReference($placeOrderData->cart_reference)
            ->get();
    }

    private function prepareTax(PlaceOrderData $placeOrderData): TaxZone
    {
        $billingAddressId = $placeOrderData->addresses->billing;

        $address = Address::with('state.country')->where('id', $billingAddressId)->first();

        $taxZone = Taxation::getTaxZone($address->state->country->id, $address->state->id);

        if (!$taxZone instanceof TaxZone) {
            // Log::info('No tax zone found');

            throw new BadRequestHttpException('No tax zone found');
        }

        return $taxZone;
    }

    private function prepareDiscount(PlaceOrderData $placeOrderData): ?Discount
    {
        if ($placeOrderData->discountCode) {
            $discount = Discount::whereCode($placeOrderData->discountCode)
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

    private function prepareShippingMethod(PlaceOrderData $placeOrderData): ?ShippingMethod
    {
        return ShippingMethod::where((new ShippingMethod())->getRouteKeyName(), $placeOrderData->shipping_method)->first() ?? null;
    }

    private function preparePaymentMethod(PlaceOrderData $placeOrderData): PaymentMethod
    {
        $paymentMethod = PaymentMethod::whereSlug($placeOrderData->payment_method)->first();

        if (!$paymentMethod instanceof PaymentMethod) {

            throw new BadRequestHttpException('No paymentMethod found');
        }

        return $paymentMethod;
    }
}
