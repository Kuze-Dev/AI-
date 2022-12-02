<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Taxonomy\Models\TaxonomyTerm>
 */
class TaxonomyTermFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = TaxonomyTerm::class;

    public function definition(): array
    {
        return [
            'taxonomy_id' => null,
            'name' => $this->faker->name(),
            'description' => null,
        ];
    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }
}
