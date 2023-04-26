<?php

declare(strict_types=1);

namespace Domain\Content\Database\Factories;

use Domain\Content\Models\ContentEntry;
use Domain\Support\MetaData\Database\Factories\MetaDataFactory;
use Domain\Support\RouteUrl\Database\Factories\RouteUrlFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Content\Models\ContentEntry>
 */
class ContentEntryFactory extends Factory
{
    /** Specify reference model. */
    protected $model = ContentEntry::class;

    /** Define values of model instance. */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name(),
            'data' => [],
        ];
    }

    public function configure(): self
    {
        return $this->has(MetaDataFactory::new())
            ->has(RouteUrlFactory::new());
    }
}
