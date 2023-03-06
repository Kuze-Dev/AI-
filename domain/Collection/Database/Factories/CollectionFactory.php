<?php

declare(strict_types=1);

namespace Domain\Collection\Database\Factories;

use Illuminate\Support\Str;
use Domain\Collection\Models\Collection;
use Domain\Collection\Enums\PublishBehavior;
use Illuminate\Database\Eloquent\Factories\Factory;
use Domain\Blueprint\Database\Factories\BlueprintFactory;

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
            'past_publish_date_behavior' => PublishBehavior::PRIVATE,
            'future_publish_date_behavior' => PublishBehavior::PUBLIC,
            'is_sortable' => (bool) rand(0, 1),
            'route_url' => function (array $attributes) {
                return '/'.Str::slug($attributes['name']).'/{{$slug}}';
            },
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
