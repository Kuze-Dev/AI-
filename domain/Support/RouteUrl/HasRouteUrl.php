<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl;

use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasRouteUrl
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Domain\Support\RouteUrl\Models\RouteUrl> */
    public function routeUrls(): MorphMany
    {
        return $this->morphMany(RouteUrl::class, 'model');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Domain\Support\RouteUrl\Models\RouteUrl> */
    public function activeRouteUrl(): MorphOne
    {
        return $this->routeUrls()->one()->latestOfMany('updated_at');
    }
}
