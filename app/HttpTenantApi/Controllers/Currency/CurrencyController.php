<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Currency;

use App\HttpTenantApi\Resources\CurrencyResource;
use Domain\Currency\Actions\CreateCurrencyAction;
use Domain\Currency\Actions\DestroyCurrencyAction;
use Domain\Currency\DataTransferObjects\CurrencyData;
use Domain\Currency\Models\Currency;
use Domain\Currency\Requests\CurrencyStoreRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;
use TiMacDonald\JsonApi\JsonApiResourceCollection;
use Spatie\RouteAttributes\Attributes\Middleware;

#[
    Resource('currency', apiResource: true, only: ['index']),
]
class CurrencyController
{
    public function index(): JsonApiResourceCollection
    {
        return CurrencyResource::collection(
            QueryBuilder::for(Currency::whereEnabled(true))
                ->get()
        );
    }

}
