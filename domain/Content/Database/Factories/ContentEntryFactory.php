<?php

declare(strict_types=1);

namespace Domain\Content\Database\Factories;

use Domain\Content\Models\ContentEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\RouteUrl\Database\Factories\RouteUrlFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Content\Models\ContentEntry>
 */
class ContentEntryFactory extends Factory
{
    /** Specify reference model. */
    protected $model = ContentEntry::class;

    /** Define values of model instance. */
    #[\Override]
    public function definition(): array
    {
        return [
            'title' => $this->faker->name(),
            'published_at' => now(),
            'data' => [],
        ];
    }

    #[\Override]
    public function configure(): self
    {
        return $this->has(MetaDataFactory::new())
            ->has(RouteUrlFactory::new());
    }
}
