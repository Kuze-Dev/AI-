<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl;

use Domain\Support\RouteUrl\Events\RouteUrlModelCreated;
use Domain\Support\RouteUrl\Events\RouteUrlModelUpdated;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRouteUrl
{
    public static function bootHasRouteUrl(): void
    {
        $generate = function (Model&Contracts\HasRouteUrl $model) {
            $model->{static::getRouteUrlIsOverrideColumn()} =
                $model->{static::getRouteUrlUrlColumn()} !== null &&
                $model->{static::getRouteUrlUrlColumn()} !== $model->getRouteUrlDefaultUrl();

            $model->{static::getRouteUrlUrlColumn()} ??= $model->getRouteUrlDefaultUrl();
        };

        static::creating(fn (Model&Contracts\HasRouteUrl $model) => $generate($model));
        static::updating(fn (Model&Contracts\HasRouteUrl $model) => $generate($model));

        static::created(function (Model&Contracts\HasRouteUrl $model) {
            /** @var static|Model&Contracts\HasRouteUrl $model */
            event(new RouteUrlModelCreated($model));
        });

        static::updated(function (Model&Contracts\HasRouteUrl $model) {
            /** @var static|Model&Contracts\HasRouteUrl $model */
            event(new RouteUrlModelUpdated($model));
        });
    }

    public function initializeHasRouteUrl(): void
    {
        if ( ! isset($this->casts[static::getRouteUrlIsOverrideColumn()])) {
            $this->casts[static::getRouteUrlIsOverrideColumn()] = 'bool';
        }

        if ( ! in_array(static::getRouteUrlUrlColumn(), $this->fillable)) {
            $this->fillable[] = static::getRouteUrlUrlColumn();
        }
        if ( ! in_array(static::getRouteUrlIsOverrideColumn(), $this->fillable)) {
            $this->fillable[] = static::getRouteUrlIsOverrideColumn();
        }
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphMany<\Domain\Support\RouteUrl\Models\RouteUrl> */
    public function routeUrls(): MorphMany
    {
        return $this->morphMany(RouteUrl::class, 'model');
    }

    public function getRouteUrlUrl(): string
    {
        return $this->{static::getRouteUrlUrlColumn()};
    }

    public function getRouteUrlIsOverride(): bool
    {
        return $this->{static::getRouteUrlIsOverrideColumn()};
    }

    public static function getRouteUrlUrlColumn(): string
    {
        return 'route_url';
    }

    public static function getRouteUrlIsOverrideColumn(): string
    {
        return 'route_url_is_override';
    }
}
