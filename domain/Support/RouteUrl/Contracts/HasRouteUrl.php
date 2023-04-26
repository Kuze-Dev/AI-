<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Support\RouteUrl\Models\RouteUrl[] $routeUrls
 * @property-read \Domain\Support\RouteUrl\Models\RouteUrl $activeRouteUrl
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface HasRouteUrl
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Domain\Support\RouteUrl\Models\RouteUrl> */
    public function routeUrls(): MorphMany;

    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Domain\Support\RouteUrl\Models\RouteUrl> */
    public function activeRouteUrl(): MorphOne;

    public static function generateRouteUrl(Model $model, array $attributes): string;
}
