<?php

declare(strict_types=1);

namespace Support\RouteUrl\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Support\RouteUrl\Models\RouteUrl;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Support\RouteUrl\Models\RouteUrl>
 */
class RouteUrlFactory extends Factory
{
    protected $model = RouteUrl::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'model_id' => null,
            'model_type' => null,
            'url' => '/'.$this->faker->unique()->word(),
            'is_override' => false,
        ];
    }
}
