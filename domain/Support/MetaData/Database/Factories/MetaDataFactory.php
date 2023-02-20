<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\Database\Factories;

use Domain\Support\MetaData\Contracts\HasMetaData;
use Domain\Support\MetaData\Models\MetaData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @extends Factory<MetaData>
 */
class MetaDataFactory extends Factory
{
    protected $model = MetaData::class;

    public function definition(): array
    {
        return [
            'model_id' => null,
            'model_type' => null,
            'title' => $this->faker->sentence(),
            'keywords' => implode(', ', Arr::wrap($this->faker->words())),
            'author' => $this->faker->name(),
            'description' => $this->faker->paragraph(),
        ];
    }

    public function configure(): self
    {
        return $this->state(function (array $attributes, ?Model $model) {
            return $model instanceof HasMetaData
                ? array_merge(
                    $attributes,
                    [
                        'title' => $model->defaultMetaData()['title'] ?? $attributes['title'],
                        'keywords' => $model->defaultMetaData()['keywords'] ?? $attributes['keywords'],
                        'author' => $model->defaultMetaData()['author'] ?? $attributes['author'],
                        'description' => $model->defaultMetaData()['description'] ?? $attributes['description'],
                    ]
                )
                : $attributes;
        });
    }
}
