<?php

declare(strict_types=1);

namespace Domain\Globals\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Globals\Models\Globals;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Globals\Models\Globals>
 */
class GlobalsFactory extends Factory
{
    /** Specify reference model. */
    protected $model = Globals::class;

    /** Define values of model instance. */
    #[\Override]
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'blueprint_id' => null,
            'data' => [],
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
