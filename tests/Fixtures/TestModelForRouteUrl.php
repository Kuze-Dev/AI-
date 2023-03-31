<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Domain\Support\RouteUrl\Contracts\HasRouteUrl as HasRouteUrlContract;
use Domain\Support\RouteUrl\HasRouteUrl;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property string $name
 * @property string $slug
 * @mixin \Eloquent
 */
class TestModelForRouteUrl extends Model implements HasRouteUrlContract
{
    use HasRouteUrl;
    use HasSlug;

    protected $fillable = ['name', 'slug'];

    public function getTable(): string
    {
        return 'test_model_for_route_url';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }

    public function getRouteUrlDefaultUrl(): string
    {
        if ($this->exists) {
            $this->generateSlugOnUpdate();
        } else {
            $this->generateSlugOnCreate();
        }

        return $this->{$this->getSlugOptions()->slugField};
    }
}
