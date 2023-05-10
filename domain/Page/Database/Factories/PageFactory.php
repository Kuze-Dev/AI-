<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Carbon\Carbon;
use Domain\Page\Models\Page;
use Domain\Page\Models\Block;
use Domain\Support\MetaData\Database\Factories\MetaDataFactory;
use Domain\Support\RouteUrl\Database\Factories\RouteUrlFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Page\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'published_at' => null,
            'locale' => 'en'
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

    public function configure(): self
    {
        return $this->has(MetaDataFactory::new(), 'metaData')
            ->has(RouteUrlFactory::new());
    }
}
