<?php

declare(strict_types=1);

namespace Domain\Product\Database\Factories;

use Domain\Product\Models\ProductOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Product\Models\ProductOption>
 */
class ProductOptionFactory extends Factory
{
    protected $model = ProductOption::class;

    public function definition()
    {
        return [

            'name' => $this->faker->name,
            'product_id' => ProductFactory::new(),
        ];
    }

    public function setProductId(int $id): self
    {
        return $this->state(['product_id' => $id]);
    }
}
