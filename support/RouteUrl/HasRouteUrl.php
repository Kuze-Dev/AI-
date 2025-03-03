<?php

declare(strict_types=1);

namespace Support\RouteUrl;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Support\RouteUrl\Models\RouteUrl;

/**
 * @template TRelatedModel of \Support\RouteUrl\Models\RouteUrl
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\MorphOneOrMany<TRelatedModel, TDeclaringModel, ?TRelatedModel>
 */
trait HasRouteUrl
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<TRelatedModel, TDeclaringModel>
     */
    public function routeUrls(): MorphOne
    {
        /** @phpstan-ignore-next-line */
        return $this->morphOne(RouteUrl::class, 'model');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<TRelatedModel, TDeclaringModel>
     */
    public function activeRouteUrl(): MorphOne
    {
        /** @phpstan-ignore-next-line */
        return $this->routeUrls()->latestOfMany('updated_at');
    }
}
