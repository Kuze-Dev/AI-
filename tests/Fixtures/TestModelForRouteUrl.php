<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContract;
use Support\RouteUrl\HasRouteUrl;

/**
 * @property string $name
 *
 * @mixin \Eloquent
 */
class TestModelForRouteUrl extends Model implements HasRouteUrlContract
{
    use HasRouteUrl;

    protected $fillable = ['name'];

    #[\Override]
    public function getTable(): string
    {
        return 'test_model_for_route_url';
    }

    #[\Override]
    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return Str::slug($model->name);
    }
}
