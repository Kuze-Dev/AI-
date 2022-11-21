<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

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
            'past_behavior' => null,
            'future_behavior' => null,
            'data' => null,
            'published_at' => null,
        ];
    }

    public function withPublishedAtBehavior(): self
    {
        return $this->state(function (array $definition) {
            $definition['past_behavior'] = Arr::random(PageBehavior::cases())->value;
            $definition['future_behavior'] = Arr::random(PageBehavior::cases())->value;

            return $definition;
        });
    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }
}
