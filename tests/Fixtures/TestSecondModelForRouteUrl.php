<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContract;
use Support\RouteUrl\HasRouteUrl;

/**
 * @property string $name
 *
 * @mixin \Eloquent
 */
class TestSecondModelForRouteUrl extends Model implements HasRouteUrlContract
{
    use HasRouteUrl;

    protected $fillable = ['name'];

    #[\Override]
    public function getTable(): string
    {
        return 'test_model_second_for_route_url';
    }

    #[\Override]
    public static function generateRouteUrl(Model $model, array $attributes): string
    {
        return $model->name;
    }
}
