<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\CMS\CMSBase;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\Actions\GetShippingRateAction;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;

// use TiMacDonald\JsonApi\JsonApiResourceCollection;

// #[Middleware('feature.tenant:'. CMSBase::class)]
class RateController
{
    #[Get('/shipping/{slug}/rate')]
    public function __invoke(string $slug)
    {

        dump(
            app(GetShippingRateAction::class)->execute(
                new ParcelData(
                    pounds: '10',
                    ounces: '0'
                ),
                $slug
            )
        );

    }
}
