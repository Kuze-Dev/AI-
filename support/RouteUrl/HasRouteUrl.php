<?php

declare(strict_types=1);

namespace Support\RouteUrl;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Support\RouteUrl\Models\RouteUrl;

/**
 * @template TRelatedModel of \Support\RouteUrl\Models\RouteUrl
 * @template THasRouteModel as \Illuminate\Database\Eloquent\Model
 *
 * @mixin THasRouteModel
 */
trait HasRouteUrl
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<TRelatedModel, THasRouteModel>
     */
    public function routeUrls(): MorphOne
    {
        /** @phpstan-ignore return.type */
        return $this->morphOne(RouteUrl::class, 'model');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<TRelatedModel, THasRouteModel>
     */
    public function activeRouteUrl(): MorphOne
    {
        return $this->routeUrls()->latestOfMany('updated_at');
    }
}
