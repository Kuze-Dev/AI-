<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Contracts;

use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Support\RouteUrl\Models\RouteUrl[] $routeUrls
 */
interface HasRouteUrl
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Domain\Support\RouteUrl\Models\RouteUrl> */
    public function routeUrls(): MorphMany;

    public function getRouteUrlDefaultUrl(): string;

    public function getActiveRouteUrl(): RouteUrl;
}
