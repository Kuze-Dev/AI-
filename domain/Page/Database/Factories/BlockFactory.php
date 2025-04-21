<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Models\Block;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Page\Models\Block>
 */
class BlockFactory extends Factory
{
    protected $model = Block::class;

    #[\Override]
    public function definition(): array
    {
        /** @var string $name */
        $name = $this->faker->words(3, true);

        return [
            'blueprint_id' => null,
            'data' => null,
            'is_fixed_content' => false,
            'name' => $name,
            'component' => Str::camel($name),
        ];
    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }
}
