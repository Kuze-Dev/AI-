<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl;

use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;

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

    /**
     * @param static|Builder<static>|Relation<static> $query
     *
     * @return Relation<static>|Builder<static>
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): Relation|Builder
    {
        /**
         * @phpstan-ignore-next-line
         *
         * The next line is ignore due to the framework's inconsistent typings
         */
        return $query->where($field ?? $this->getRouteKeyName(), $value)
            ->orWhereRelation('routeUrls', 'url', $value);
    }
}
