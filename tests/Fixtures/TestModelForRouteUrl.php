<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContract;
use Domain\Support\RouteUrl\HasRouteUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $name
 * @mixin \Eloquent
 */
class TestModelForRouteUrl extends Model implements HasRouteUrlContract
{
    use HasRouteUrl;

    protected $fillable = ['name'];

    public function getTable(): string
    {
        return 'test_model_for_route_url';
    }

    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return Str::slug($model->name);
    }
}
