<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Site;

use App\HttpTenantApi\Resources\SiteResource;
use Domain\Site\Models\Site;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('sites', only: ['index'])]
class SiteController
{
    public function index(): JsonApiResourceCollection
    {
        return SiteResource::collection(
            QueryBuilder::for(Site::query())
                ->allowedFilters(['id', 'name'])
                ->jsonPaginate()
        );
    }
}
