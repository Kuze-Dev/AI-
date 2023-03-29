<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\RouteUrl;

use App\HttpTenantApi\Resources\RouteUrlResource;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Spatie\RouteAttributes\Attributes\Get;

class RouteUrlController
{
    #[Get('/route/{routeUrl}')]
    public function __invoke(string $routeUrl)
    {
        return RouteUrlResource::make(
            RouteUrl::where('url', $routeUrl)
                ->firstOrFail()
        );
    }
}
