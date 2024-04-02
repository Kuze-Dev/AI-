<?php

declare(strict_types=1);

namespace Domain\Blueprint\Database\Factories;

use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Blueprint\Models\Blueprint>
 */
class BlueprintFactory extends Factory
{
    protected $model = Blueprint::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'schema' => [],
        ];
    }

    public function withDummySchema(): self
    {
        $instance = $this;

        foreach (range(1, rand(1, 3)) as $i) {
            $instance = $instance->addSchemaSection();

            foreach (range(1, rand(1, 3)) as $i) {
                $instance = $instance->addSchemaField();
            }
        }

        return $instance;
    }

    public function addSchemaSection(array $attributes = []): self
    {
        return $this->state(function (array $definition) use ($attributes) {
            if (! isset($definition['schema']['sections'])) {
                $definition['schema']['sections'] = [];
            }

            $definition['schema']['sections'][] = array_merge(
                [
                    'title' => fake()->word(),
                    'fields' => [],
                ],
                $attributes
            );

            return $definition;
        });
    }

    public function addSchemaField(array $attributes = []): self
    {
        return $this->state(function (array $definition) use ($attributes) {
            $definition['schema']['sections'][count($definition['schema']['sections']) - 1]['fields'][] = array_merge(
                [
                    'title' => fake()->word(),
                    'type' => FieldType::TEXT,
                ],
                $attributes
            );

            return $definition;
        });
    }

    public function addMediaSchemaField(array $attributes = []): self
    {
        return $this->state(function (array $definition) use ($attributes) {
            $definition['schema']['sections'][count($definition['schema']['sections']) - 1]['fields'][] = array_merge(
                [
                    'title' => fake()->word(),
                    'type' => FieldType::MEDIA,
                ],
                $attributes
            );

            return $definition;
        });
    }
}
