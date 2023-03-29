<?php

declare(strict_types=1);

namespace Domain\Collection\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Collection\Enums\PublishBehavior;
use Domain\Collection\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Collection\Models\Collection>
 */
class CollectionFactory extends Factory
{
    /** Specify reference model. */
    protected $model = Collection::class;

    /** Define values of model instance. */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'blueprint_id' => null,
            'name' => $name,
            'past_publish_date_behavior' => null,
            'future_publish_date_behavior' => null,
            'is_sortable' => false,
            'route_url' => function (array $attributes) {
                return '/' . Str::slug($attributes['name']) . '/{{$slug}}';
            },
        ];
    }

    public function publishDateBehaviour(
        PublishBehavior $pastPublishDateBehaviour = PublishBehavior::PUBLIC,
        PublishBehavior $futurePublishDateBehaviour = PublishBehavior::PRIVATE
    ): self {
        return $this->state([
            'past_publish_date_behavior' => $pastPublishDateBehaviour,
            'future_publish_date_behavior' => $futurePublishDateBehaviour,
        ]);
    }

    public function sortable(bool $state = true): self
    {
        return $this->state(['is_sortable' => $state]);
    }

    /**
     * Bind a blueprint record
     * to current model.
     */
    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }
}
