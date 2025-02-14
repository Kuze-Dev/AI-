<?php

declare(strict_types=1);

namespace Support\RouteUrl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Support\RouteUrl\Models\RouteUrl;

trait HasRouteUrl
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<Model, $this> */
    public function routeUrls(): MorphOne
    {
        return $this->morphOne(RouteUrl::class, 'model');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<Model> */
    public function activeRouteUrl(): MorphOne
    {
        return $this->routeUrls()->latestOfMany('updated_at');
    }
}
