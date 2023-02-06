<?php

declare(strict_types=1);

namespace Domain\Collection\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Collection\Enums\PublishBehavior;
use Domain\Collection\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'blueprint_id' => null,
            'name' => $this->faker->name(),
            'past_publish_date_behavior' => PublishBehavior::PRIVATE,
            'future_publish_date_behavior' => PublishBehavior::PUBLIC,
            'is_sortable' => (bool) rand(0, 1),
            'route_url' => substr(str_shuffle(
                str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1, 8))
            ), 1, 8),
        ];
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
