<?php

declare(strict_types=1);

namespace Domain\Blueprint\Database\Factories;

use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Enums\FieldType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Blueprint\Models\Blueprint>
 */
class BlueprintFactory extends Factory
{
    protected $model = Blueprint::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'schema' => [],
        ];
    }

    public function withDummySchema(?array $sections = [], ?array $fields = []): self
    {
        $instance = $this;

        if ( ! empty($sections)) {
            foreach ($sections as $key => $section) {
                $instance = $instance->addSchemaSection($section ?? [])
                    ->addSchemaField($fields[$key] ?? []);
            }
        } else {
            if ( ! empty($fields)) {
                foreach ($fields as $field) {
                    $instance = $instance->addSchemaSection()
                        ->addSchemaField($field);
                }
            } else {
                $instance = $instance->addSchemaSection()
                    ->addSchemaField();
            }
        }

        return $instance;
    }

    public function addSchemaSection(array $attributes = []): self
    {
        return $this->state(function (array $definition) use ($attributes) {
            if ( ! isset($definition['schema']['sections'])) {
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
}
