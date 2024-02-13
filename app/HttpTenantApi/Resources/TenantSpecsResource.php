<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\Features;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Tenant\Models\Tenant
 */
class TenantSpecsResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'domains' => $this->domains->pluck('domain')->toArray(),
            'features' => [
                'cms' => [
                    app(Features\CMS\Internationalization::class)->getLabel() => $this->features()->active(Features\CMS\Internationalization::class),
                    app(Features\CMS\SitesManagement::class)->getLabel() => $this->features()->active(Features\CMS\SitesManagement::class),
                ],
                'customer' => [
                    app(Features\Customer\TierBase::class)->getLabel() => $this->features()->active(Features\Customer\TierBase::class),
                    app(Features\Customer\AddressBase::class)->getLabel() => $this->features()->active(Features\Customer\AddressBase::class),
                ],
                'e-commerce' => [
                    app(Features\ECommerce\AllowGuestOrder::class)->getLabel() => $this->features()->active(Features\ECommerce\AllowGuestOrder::class),
                    app(Features\ECommerce\RewardPoints::class)->getLabel() => $this->features()->active(Features\ECommerce\RewardPoints::class),
                ],
                'shop-configuration' => [

                    app(Features\Shopconfiguration\TaxZone::class)->getLabel() => $this->features()->active(Features\Shopconfiguration\TaxZone::class),
                    'payments' => [
                        app(Features\Shopconfiguration\PaymentGateway\PaypalGateway::class)->getLabel() => $this->features()->active(Features\Shopconfiguration\PaymentGateway\PaypalGateway::class),
                        app(Features\Shopconfiguration\PaymentGateway\StripeGateway::class)->getLabel() => $this->features()->active(Features\Shopconfiguration\PaymentGateway\StripeGateway::class),
                        app(Features\Shopconfiguration\PaymentGateway\OfflineGateway::class)->getLabel() => $this->features()->active(Features\Shopconfiguration\PaymentGateway\OfflineGateway::class),
                        app(Features\Shopconfiguration\PaymentGateway\BankTransfer::class)->getLabel() => $this->features()->active(Features\Shopconfiguration\PaymentGateway\BankTransfer::class),
                    ],
                    'shipping' => [
                        app(Features\Shopconfiguration\Shipping\ShippingStorePickup::class)->getLabel() => $this->features()->active(Features\Shopconfiguration\Shipping\ShippingStorePickup::class),
                        app(Features\Shopconfiguration\Shipping\ShippingUsps::class)->getLabel() => $this->features()->active(Features\Shopconfiguration\Shipping\ShippingUsps::class),
                        app(Features\Shopconfiguration\Shipping\ShippingUps::class)->getLabel() => $this->features()->active(Features\Shopconfiguration\Shipping\ShippingUps::class),
                        app(Features\Shopconfiguration\Shipping\ShippingAusPost::class)->getLabel() => $this->features()->active(Features\Shopconfiguration\Shipping\ShippingAusPost::class),
                    ],
                ],

            ],
        ];
    }
}
