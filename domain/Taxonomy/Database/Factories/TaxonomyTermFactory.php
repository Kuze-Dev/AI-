<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Database\Factories;

use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Taxonomy\Models\TaxonomyTerm>
 */
class TaxonomyTermFactory extends Factory
{
    protected $model = TaxonomyTerm::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'taxonomy_id' => null,
            'parent_id' => null,
            'name' => $this->faker->name(),
            'order' => null,
            'data' => [],
        ];
    }
}
