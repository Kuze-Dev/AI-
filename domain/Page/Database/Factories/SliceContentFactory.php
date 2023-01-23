<?php

declare(strict_types=1);

namespace Domain\Page\Database\Factories;

use Domain\Page\Models\SliceContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Page\Models\SliceContent>
 */
class SliceContentFactory extends Factory
{
    protected $model = SliceContent::class;

    public function definition(): array
    {
        return [
            'page_id' => null,
            'slice_id' => null,
            'data' => [],
        ];
    }
}
