<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $randomBehavior = function (array $attributes): ?string {
            if ($attributes['published_at'] === null) {
                return null;
            }

            return Arr::random(PageBehavior::cases())->value;
        };

        return [
            'blueprint_id' => BlueprintFactory::new()->withDummySchema(),
            'name' => $this->faker->name(),
            'past_behavior' => $randomBehavior,
            'future_behavior' => $randomBehavior,
            'data' => null,
            'published_at' => $this->faker->boolean() ? now() : null,
        ];
    }

    public function withOutPublishedAtBehavior(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => null,
            ];
        });
    }

    public function withPublishedAtBehavior(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => now(),
            ];
        });
    }
}
