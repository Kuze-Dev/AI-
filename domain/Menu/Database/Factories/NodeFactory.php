<?php

declare(strict_types=1);

namespace Domain\Menu\Database\Factories;

use Domain\Menu\Enums\Target;
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
            'menu_id' => null,
            'parent_id' => null,
            'label' => fake()->word(),
            'target' => Target::SELF,
            'url' => fake()->url(),
            'order' => null,
        ];
    }
}
