<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Taxonomy\Models\Taxonomy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Taxonomy\Models\Taxonomy>
 */
class TaxonomyFactory extends Factory
{
    protected $model = Taxonomy::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'has_route' => false,
        ];
    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }

    public function setBlueprintId(string $id): self
    {
        return $this->state(['blueprint_id' => $id]);
    }
}
