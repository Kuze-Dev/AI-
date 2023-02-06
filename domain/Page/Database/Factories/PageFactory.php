<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Domain\Page\Models\Page;
use Domain\Page\Models\Slice;
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
            'route_url' => $this->faker->word(),
        ];
    }

    public function addSliceContent(Slice|SliceFactory $slice, array $attributes = []): self
    {
        return $this->has(SliceContentFactory::new($attributes)->for($slice));
    }
}
