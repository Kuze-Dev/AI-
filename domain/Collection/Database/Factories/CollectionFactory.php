<?php 

declare (strict_types = 1);

namespace Domain\Collection\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Collection\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;


class CollectionFactory extends Factory 
{
    /**
     * Specify reference model.
     * 
     * @var string
     */
    protected $model = Collection::class;

    /**
     * Define values of model instance.
     * 
     * @return array
     */
    public function definition(): array 
    {
        return [
            'blueprint_id' => null,
            'name' => $this->faker->name(),
            'past_publish_date' => 'private',
            'future_publish_date' => 'public',
            'is_sortable' => rand(0, 1)
        ];
    }

    /**
     * @return self
     */
    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }
}