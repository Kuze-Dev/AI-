<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\RouteUrl;

use App\HttpTenantApi\Resources\RouteUrlResource;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;

class RouteUrlController
{
    #[Get('/route/{routeUrl}')]
    public function __invoke(string $routeUrl): RouteUrlResource
    {
        return RouteUrlResource::make(
            QueryBuilder::for(RouteUrl::where('url', $routeUrl))
                ->allowedIncludes([
                    'model',
                ])
                ->firstOrFail()
        );
    }
}
