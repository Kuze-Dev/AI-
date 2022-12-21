<?php

declare(strict_types=1);

namespace Domain\Menu\Database\Factories;

use Domain\Menu\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Menu\Models\Node>
 */
class NodeFactory extends Factory
{
    protected $model = Node::class;

    public function definition(): array
    {
        return [
            'label' => fake()->word(),
            'target' => '_blank',
            'url' => fake()->url(),
        ];
    }
}
