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
        static::creating(function (Model&Contracts\HasRouteUrl $model) {
            /** @var static|Model&Contracts\HasRouteUrl $model */
            $model->generateRouteUrlOnCreate();
        });

        static::updating(function (Model&Contracts\HasRouteUrl $model) {
            /** @var static|Model&Contracts\HasRouteUrl $model */
            $model->generateRouteUrlOnUpdate();
        });

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

    public function routeUrls(): MorphMany
    {
        return $this->morphMany(RouteUrl::class, 'model');
    }

    private function generateRouteUrlOnCreate(): void
    {
        $this->{static::getRouteUrlIsOverrideColumn()} = $this->{static::getRouteUrlUrlColumn()} !== null &&
            $this->{static::getRouteUrlUrlColumn()} !== $this->getRouteUrlDefaultUrl();

        $this->{static::getRouteUrlUrlColumn()} ??= $this->getRouteUrlDefaultUrl();
    }

    private function generateRouteUrlOnUpdate(): void
    {
        $this->generateRouteUrlOnCreate();
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
