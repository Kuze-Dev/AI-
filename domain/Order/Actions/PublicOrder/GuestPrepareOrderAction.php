<?php

declare(strict_types=1);

namespace Domain\Order\Actions\PublicOrder;

use App\Settings\OrderSettings;
use App\Settings\SiteSettings;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Models\Currency;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Order\DataTransferObjects\GuestCountriesData;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\Order\DataTransferObjects\GuestStatesData;
use Domain\Order\Exceptions\OrderEmailSettingsException;
use Domain\Order\Exceptions\OrderEmailSiteSettingsException;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Product\Models\ProductVariant;
use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GuestPrepareOrderAction
{
    public function execute(GuestPlaceOrderData $guestPlaceOrderData): GuestPreparedOrderData
    {
        $customer = $guestPlaceOrderData->customer;

        $this->prepareSiteSettings();

        $this->prepareEmailSettings();

        $addresses = $this->prepareAddress($guestPlaceOrderData);

        $currency = $this->prepareCurrency();

        $cartLines = $this->prepareCartLines($guestPlaceOrderData);

        $countries = $this->prepareCountry($guestPlaceOrderData);

        $states = $this->prepareState($guestPlaceOrderData);

        $taxZone = $this->prepareTax($countries->billingCountry, $states->billingState);

        $discount = $this->prepareDiscount($guestPlaceOrderData);

        $paymentMethod = $this->preparePaymentMethod($guestPlaceOrderData);

        $shippingMethod = $this->prepareShippingMethod($guestPlaceOrderData);

        $shippingData = $this->prepareShippingData($guestPlaceOrderData);

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
            'shippingReceiverData' => $shippingData['receiverData'],
            'shippingAddressData' => $shippingData['shippingAddress'],
            'paymentMethod' => $paymentMethod,
            'countries' => $countries,
            'states' => $states,
        ];

        return GuestPreparedOrderData::fromArray($orderData);
    }

    public function prepareEmailSettings(): void
    {
        $fromEmail = app(OrderSettings::class)->email_sender_name;

        if (empty($fromEmail)) {
            throw new OrderEmailSettingsException('No email sender found');
        }
    }

    public function prepareSiteSettings(): void
    {
        try {
            app(SiteSettings::class)->getLogoUrl();
        } catch (Exception) {
            throw new OrderEmailSiteSettingsException('No logo for site settings found');
        }
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

        if (! $currency instanceof Currency) {

            throw new BadRequestHttpException('No currency found');
        }

        return $currency;
    }

    /**
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

    public function prepareTax(Country $country, State $state): ?TaxZone
    {
        $taxZone = Taxation::getTaxZone($country->id, $state->id);

        if (! $taxZone instanceof TaxZone) {
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
        return ShippingMethod::where((new ShippingMethod)->getRouteKeyName(), $guestPlaceOrderData->shipping_method)->first() ?? null;
    }

    public function prepareShippingData(GuestPlaceOrderData $guestPlaceOrderData): array
    {
        $receiverData = new ReceiverData(
            first_name: $guestPlaceOrderData->customer->first_name,
            last_name: $guestPlaceOrderData->customer->last_name,
            email: $guestPlaceOrderData->customer->email,
            mobile: $guestPlaceOrderData->customer->mobile,
        );

        $stateId = $guestPlaceOrderData->addresses->shipping->state_id;
        $countryId = $guestPlaceOrderData->addresses->shipping->country_id;

        /** @var \Domain\Address\Models\State $state */
        $state = app(State::class)->where((new State)->getRouteKeyName(), $stateId)->first();

        /** @var \Domain\Address\Models\Country $country */
        $country = app(Country::class)->where((new Country)->getRouteKeyName(), $countryId)->first();

        $shippingAddress = new ShippingAddressData(
            address: $guestPlaceOrderData->addresses->shipping->address_line_1,
            city: $guestPlaceOrderData->addresses->shipping->city,
            zipcode: $guestPlaceOrderData->addresses->shipping->zip_code,
            code: $state->code,
            state: $state,
            country: $country,
        );

        return [
            'receiverData' => $receiverData,
            'shippingAddress' => $shippingAddress,
        ];
    }

    public function preparePaymentMethod(GuestPlaceOrderData $guestPlaceOrderData): PaymentMethod
    {
        $paymentMethod = PaymentMethod::whereSlug($guestPlaceOrderData->payment_method)->first();

        if (! $paymentMethod instanceof PaymentMethod) {

            throw new BadRequestHttpException('No paymentMethod found');
        }

        return $paymentMethod;
    }

    public function prepareState(GuestPlaceOrderData $guestPlaceOrderData): GuestStatesData
    {
        $shippingStateId = $guestPlaceOrderData->addresses->shipping->state_id;
        $billingStateId = $guestPlaceOrderData->addresses->billing->state_id;

        /** @var \Domain\Address\Models\State $shippingState */
        $shippingState = app(State::class)->where((new State)->getRouteKeyName(), $shippingStateId)->first();

        /** @var \Domain\Address\Models\State $billingState */
        $billingState = app(State::class)->where((new State)->getRouteKeyName(), $billingStateId)->first();

        $stateData = [
            'shippingState' => $shippingState,
            'billingState' => $billingState,
        ];

        return GuestStatesData::fromArray($stateData);
    }

    public function prepareCountry(GuestPlaceOrderData $guestPlaceOrderData): GuestCountriesData
    {
        $shippingCountryId = $guestPlaceOrderData->addresses->shipping->country_id;
        $billingCountryId = $guestPlaceOrderData->addresses->billing->country_id;

        /** @var \Domain\Address\Models\Country $shippingCountry */
        $shippingCountry = app(Country::class)->where((new Country)->getRouteKeyName(), $shippingCountryId)->first();

        /** @var \Domain\Address\Models\Country $billingCountry */
        $billingCountry = app(Country::class)->where((new Country)->getRouteKeyName(), $billingCountryId)->first();

        $countryData = [
            'shippingCountry' => $shippingCountry,
            'billingCountry' => $billingCountry,
        ];

        return GuestCountriesData::fromArray($countryData);
    }
}
