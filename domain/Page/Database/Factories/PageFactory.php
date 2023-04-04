<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Domain\Page\Models\Page;
use Domain\Page\Models\Slice;
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
        ];
    }

    public function addSliceContent(Slice|SliceFactory $slice, array $attributes = []): self
    {
        return $this->has(SliceContentFactory::new($attributes)->for($slice));
    }

    public function configure(): self
    {
        return $this->has(MetaDataFactory::new(), 'metaData')
            ->has(RouteUrlFactory::new());
    }
}
