<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\CMS\CMSBase;
use Domain\Address\Models\Address;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\Actions\GetShippingRateAction;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;
use Throwable;

// use TiMacDonald\JsonApi\JsonApiResourceCollection;

// #[Middleware('feature.tenant:'. CMSBase::class)]
class RateController
{
    #[Get('/shipping/{slug}/rate/{addressId}')]
    public function __invoke(string $slug, string $addressId)
    {

        try {

            $customerShippingAddress = Address::with('state')->where('customer_id', '1')
                ->where('id', $addressId)->firstOrFail();

            dump(
                app(GetShippingRateAction::class)->execute(
                    new ParcelData(
                        pounds: '10',
                        ounces: '0'
                    ),
                    $customerShippingAddress,
                    $slug
                )
            );
        } catch (Throwable $th) {

            throw $th;
        }

    }
}
