<?php

declare(strict_types=1);

namespace Domain\Collection\Database\Factories;

use Domain\Collection\Models\CollectionEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionEntryFactory extends Factory
{
    /**
     * Specify reference model.
     *
     * @var string
     */
    protected $model = CollectionEntry::class;

    /** Define values of model instance. */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name(),
        ];
    }
}
