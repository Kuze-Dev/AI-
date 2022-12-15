<?php

declare(strict_types=1);

namespace Domain\Menu\Database\Factories;

use Domain\Menu\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Node\Models\Node>
 */
class NodeFactory extends Factory
{
    protected $model = Node::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'url' => fake()->url(),
            'target' => '_blank'
        ];
    }
}
