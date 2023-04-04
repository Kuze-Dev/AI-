<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl;

use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRouteUrl
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Domain\Support\RouteUrl\Models\RouteUrl> */
    public function routeUrls(): MorphMany
    {
        return $this->morphMany(RouteUrl::class, 'model');
    }

    public function getActiveRouteUrl(): RouteUrl
    {
        /** @phpstan-ignore-next-line  */
        return $this->routeUrls->sortByDesc('created_at')->first();
    }
}
