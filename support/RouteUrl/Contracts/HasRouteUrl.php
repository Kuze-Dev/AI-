<?php

declare(strict_types=1);

namespace Support\RouteUrl\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read int $id
 * @property-read \Illuminate\Database\Eloquent\Collection|\Support\RouteUrl\Models\RouteUrl[] $routeUrls
 * @property-read \Support\RouteUrl\Models\RouteUrl|null $activeRouteUrl
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
interface HasRouteUrl
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Support\RouteUrl\Models\RouteUrl> */
    public function routeUrls(): MorphOne;

    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Support\RouteUrl\Models\RouteUrl> */
    public function activeRouteUrl(): MorphOne;

    public static function generateRouteUrl(Model $model, array $attributes): string;
}
