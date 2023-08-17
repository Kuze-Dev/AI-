<?php

declare(strict_types=1);

namespace Domain\Review\Database\Factories;

use Domain\Review\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Review\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'customer_id' => 1,
            'product_id' => 1,
            'order_id' => 1,
            'order_line_id' => 1,
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->numberBetween(1, 5),
            'is_anonymous' => true,
        ];
    }

    public function setCustomerId(int $id): self
    {
        return $this->state(['customer_id' => $id]);
    }

    public function setProductId(int $id): self
    {
        return $this->state(['product_id' => $id]);
    }

    public function setOrderId(int $id): self
    {
        return $this->state(['order_id' => $id]);
    }

    public function setOrderLineId(int $id): self
    {
        return $this->state(['order_line_id' => $id]);
    }
}
