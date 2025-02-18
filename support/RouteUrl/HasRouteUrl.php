<?php

declare(strict_types=1);

namespace Support\RouteUrl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Support\RouteUrl\Models\RouteUrl;

trait HasRouteUrl
{
    /**
     * @template TRelatedModel of \Support\RouteUrl\Models\RouteUrl
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<Model, $this>
     */
    public function routeUrls(): MorphOne
    {
        /** @phpstan-ignore-next-line */
        return $this->morphOne(RouteUrl::class, 'model');
    }

    /**
     * @template TRelatedModel of \Support\RouteUrl\Models\RouteUrl
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<Model, $this>
     */
    public function activeRouteUrl(): MorphOne
    {
        /** @phpstan-ignore-next-line */
        return $this->routeUrls()->latestOfMany('updated_at');
    }
}
