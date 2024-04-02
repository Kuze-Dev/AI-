<?php

declare(strict_types=1);

namespace Domain\Menu\Database\Factories;

use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;
use Domain\Menu\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Menu\Models\Node>
 */
class NodeFactory extends Factory
{
    protected $model = Node::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'menu_id' => null,
            'parent_id' => null,
            'model_type' => null,
            'model_id' => null,
            'label' => fake()->word(),
            'target' => Target::self,
            'type' => function (array $attributes) {
                return $attributes['model_type'] === null
                    ? NodeType::URL
                    : NodeType::RESOURCE;
            },
            'url' => function (array $attributes) {
                return $attributes['model_type'] === null
                    ? fake()->url()
                    : null;
            },
            'order' => null,
        ];
    }
}
