<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Address\Models\Address;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Models\Currency;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Enums\OrderResult;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Product\Models\ProductVariant;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Log;

class PrepareOrderAction
{
    public function execute(PlaceOrderData $placeOrderData)
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

    private function prepareAddress(PlaceOrderData $placeOrderData)
    {
        $shippingAddress = Address::with('state.country')->find($placeOrderData->addresses->shipping);

        $billingAddress = Address::with('state.country')->find($placeOrderData->addresses->billing);

        return [
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
        ];
    }

    private function prepareCurrency()
    {
        return Currency::where('default', true)->first();
    }

    private function prepareCartLines(PlaceOrderData $placeOrderData)
    {
        return CartLine::with(['purchasable' => function (MorphTo $query) {
            $query->morphWith([
                ProductVariant::class => ['product'],
            ]);
        },])
            ->whereCheckoutReference($placeOrderData->cart_reference)
            ->get();
    }

    private function prepareTax(PlaceOrderData $placeOrderData)
    {
        $taxZone = Taxation::getTaxZone($placeOrderData->taxation_data->country_id, $placeOrderData->taxation_data->state_id);

        if (!$taxZone instanceof TaxZone) {
            Log::info('No tax zone found');

            throw new BadRequestHttpException('No tax zone found');
            // return OrderResult::FAILED;
        }

        return $taxZone;
    }

    private function prepareDiscount(PlaceOrderData $placeOrderData)
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

    private function prepareShippingMethod(PlaceOrderData $placeOrderData)
    {
        return ShippingMethod::where((new ShippingMethod())->getRouteKeyName(), $placeOrderData->shipping_method)->first();
    }

    private function preparePaymentMethod(PlaceOrderData $placeOrderData)
    {
        return PaymentMethod::whereSlug($placeOrderData->payment_method)->first();
    }
}
