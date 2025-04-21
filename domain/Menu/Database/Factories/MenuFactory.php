<?php

declare(strict_types=1);

namespace Domain\Menu\Database\Factories;

use Domain\Menu\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Menu\Models\Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }
}
