<?php

declare(strict_types=1);

namespace Domain\Product\Database\Factories;

use Domain\Product\Models\ProductOptionValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Product\Models\ProductOptionValue>
 */
class ProductOptionValueFactory extends Factory
{
    protected $model = ProductOptionValue::class;

    #[\Override]
    public function definition()
    {
        return [

            'name' => $this->faker->name,
            'product_option_id' => $this->faker->numberBetween(1, 5),
        ];
    }

    public function setProductOptionId(int $id): self
    {
        return $this->state(['product_option_id' => $id]);
    }
}
