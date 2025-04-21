<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Domain\Page\Models\BlockContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Page\Models\BlockContent>
 */
class BlockContentFactory extends Factory
{
    protected $model = BlockContent::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'page_id' => null,
            'block_id' => null,
            'data' => [],
        ];
    }
}
