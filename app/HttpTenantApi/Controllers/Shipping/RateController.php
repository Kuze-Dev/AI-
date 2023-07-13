<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\PageResource;
use Carbon\Carbon;
use Domain\Page\Models\Builders\PageBuilder;
use Domain\Page\Models\Page;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\Drivers\UspsDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

// #[Middleware('feature.tenant:'. CMSBase::class)]
class RateController
{
    #[Get('/shipping/{slug}/rate')]
    public function __invoke(string $slug): JsonApiResourceCollection
    {

        $usps = new UspsDriver(AddressValidateRequestData::fromArray([
            'Address1' => '',
            'Address2' => 'STE K 185 Berry Street',
            'City' => 'San Francisco',
            'State' => 'CA',
            'Zip5' => '5656',
            'Zip4' => '2342',
        ]));

        dd($usps->getRate());

     
    }

  
}
