<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Database\Factories;

use Domain\Taxonomy\Models\Taxonomy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Taxonomy\Models\Taxonomy>
 */
class TaxonomyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Taxonomy::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
