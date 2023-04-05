<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContract;
use Domain\Support\RouteUrl\HasRouteUrl;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @mixin \Eloquent
 */
class TestSecondModelForRouteUrl extends Model implements HasRouteUrlContract
{
    use HasRouteUrl;

    protected $fillable = ['name'];

    public function getTable(): string
    {
        return 'test_model_second_for_route_url';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getRouteUrlDefaultUrl(): string
    {
        return $this->name;
    }
}
