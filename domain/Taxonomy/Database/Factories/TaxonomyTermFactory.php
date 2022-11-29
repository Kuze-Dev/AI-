<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Taxonomy\Models\Taxonomy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Page\Models\Page>
 */
class TaxonomyFactory extends Factory
{
    protected $model = Taxonomy::class;

    public function definition(): array
    {
        return [
            'taxonomy_id' => null,
            'name' => $this->faker->name(),
        ];
    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }
}
