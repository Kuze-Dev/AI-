<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\Database\Factories;

use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Support\RouteUrl\Models\RouteUrl>
 */
class RouteUrlFactory extends Factory
{
    protected $model = RouteUrl::class;

    public function definition(): array
    {
        return [
            'model_id' => null,
            'model_type' => null,
            'url' => $this->faker->unique()->word(),
            'is_override' => $this->faker->boolean(),
        ];
    }
}
