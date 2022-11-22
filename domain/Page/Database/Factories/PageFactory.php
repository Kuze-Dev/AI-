<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Models\Page;
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
            'blueprint_id' => null,
            'name' => $this->faker->name(),
            'data' => null,
        ];
    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }

    public function publicPublished(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => now(),
                'past_behavior' => PageBehavior::PUBLIC,
                'future_behavior' => PageBehavior::PUBLIC,
            ];
        });
    }
}
