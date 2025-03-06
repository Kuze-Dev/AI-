<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use App\Settings\OrderSettings;
use App\Settings\SiteSettings;
use Domain\Address\Models\Address;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Models\Currency;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Exceptions\OrderEmailSettingsException;
use Domain\Order\Exceptions\OrderEmailSiteSettingsException;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Domain\Tier\Models\Tier;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PrepareOrderAction
{
    public function execute(PlaceOrderData $placeOrderData): PreparedOrderData
    {
        $customer = auth()->user();

        $this->prepareSiteSettings();

        $this->prepareEmailSettings();

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

    public function prepareAddress(PlaceOrderData $placeOrderData): array
    {
        $shippingAddress = Address::with('state.country')->find($placeOrderData->addresses->shipping);

        $billingAddress = Address::with('state.country')->find($placeOrderData->addresses->billing);

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
    public function prepareCartLines(PlaceOrderData $placeOrderData): Collection
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        /** @var \Domain\Tier\Models\Tier $tier */
        $tier = $customer->tier ?? Tier::query()->where('name', config('domain.tier.default'))->first();

        return CartLine::with(['purchasable' => function (MorphTo $query) use ($tier) {
            $query->morphWith([
                Product::class => [
                    'productTier' => function (BelongsToMany $query) use ($tier) {
                        $query->where('tier_id', $tier->id);
                    },
                ],
                ProductVariant::class => [
                    'product.productTier' => function (BelongsToMany $query) use ($tier) {
                        $query->where('tier_id', $tier->id);
                    },
                ],
            ]);
        }, ])
            ->whereCheckoutReference($placeOrderData->cart_reference)
            ->get();
    }

    public function prepareTax(PlaceOrderData $placeOrderData): ?TaxZone
    {
        $billingAddressId = $placeOrderData->addresses->billing;

        /** @var \Domain\Address\Models\Address $address */
        $address = Address::with('state.country')->where('id', $billingAddressId)->first();

        /** @var \Domain\Address\Models\State $state */
        $state = $address->state;

        /** @var \Domain\Address\Models\Country $country */
        $country = $state->country;

        $taxZone = Taxation::getTaxZone($country->id, $state->id);

        if (! $taxZone instanceof TaxZone) {
            // Log::info('No tax zone found');
            return null;
            // throw new BadRequestHttpException('No tax zone found');
        }

        return $taxZone;
    }

    public function prepareDiscount(PlaceOrderData $placeOrderData): ?Discount
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

    public function prepareShippingMethod(PlaceOrderData $placeOrderData): ?ShippingMethod
    {
        return ShippingMethod::where((new ShippingMethod)->getRouteKeyName(), $placeOrderData->shipping_method)->first() ?? null;
    }

    public function preparePaymentMethod(PlaceOrderData $placeOrderData): PaymentMethod
    {
        $paymentMethod = PaymentMethod::whereSlug($placeOrderData->payment_method)->first();

        if (! $paymentMethod instanceof PaymentMethod) {

            throw new BadRequestHttpException('No paymentMethod found');
        }

        return $paymentMethod;
    }
}
