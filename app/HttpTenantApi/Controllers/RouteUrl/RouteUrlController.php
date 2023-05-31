<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\RouteUrl;

use App\HttpTenantApi\Resources\ContentEntryResource;
use App\HttpTenantApi\Resources\PageResource;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Support\Str;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Where;
use TiMacDonald\JsonApi\JsonApiResource;
use InvalidArgumentException;

class RouteUrlController
{
    #[
        Get('/route/{url?}'),
        Where('url', '.*')
    ]
    public function __invoke(string $url = ''): JsonApiResource
    {
        $routeUrl = RouteUrl::whereUrl(Str::start($url, '/'))
            ->with('model')
            ->firstOrFail();

        return match ($routeUrl->model::class) {
            Page::class => PageResource::make($routeUrl->model),
            ContentEntry::class => ContentEntryResource::make($routeUrl->model),
            default => throw new InvalidArgumentException('No resource found for model '.$routeUrl->model::class),
        };
    }
}
