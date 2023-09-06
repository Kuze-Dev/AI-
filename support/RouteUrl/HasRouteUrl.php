<?php

declare(strict_types=1);

namespace Support\RouteUrl;

use Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasRouteUrl
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Support\RouteUrl\Models\RouteUrl> */
    public function routeUrls(): MorphOne
    {
        return $this->morphOne(RouteUrl::class, 'model');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Support\RouteUrl\Models\RouteUrl> */
    public function activeRouteUrl(): MorphOne
    {
        return $this->routeUrls()->latestOfMany('updated_at');
    }
}
