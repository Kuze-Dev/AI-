<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Domain\Page\Enums\Visibility;
use Domain\Page\Models\Block;
use Domain\Page\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\RouteUrl\Database\Factories\RouteUrlFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Page\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'published_at' => now(),
            'visibility' => Arr::random(Visibility::cases()),
        ];
    }

    public function published(Carbon|bool $state = true): self
    {
        if ($state === false) {
            return $this;
        }

        return $this->state(['published_at' => $state instanceof Carbon ? $state : now()]);
    }

    public function addBlockContent(Block|BlockFactory $block, array $attributes = []): self
    {
        return $this->has(BlockContentFactory::new($attributes)->for($block));
    }

    #[\Override]
    public function configure(): self
    {
        return $this->afterCreating(function (Page $model) {
            if (! $model->metaData) {
                (new Relationship(MetaDataFactory::new(), 'metaData'))->recycle($this->recycle)->createFor($model);
            }

            if (! $model->activeRouteUrl) {
                (new Relationship(RouteUrlFactory::new(), 'routeUrls'))->recycle($this->recycle)->createFor($model);
            }
        });
    }
}
