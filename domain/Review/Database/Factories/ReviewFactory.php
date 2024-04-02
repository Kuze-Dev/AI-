<?php

declare(strict_types=1);

namespace Domain\Review\Database\Factories;

use Domain\Order\Database\Factories\OrderFactory;
use Domain\Review\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Review\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    #[\Override]
    public function definition(): array
    {
        $order = OrderFactory::new()->create();
        $orderId = isset($order->id) && $order->id;
        $orderLineId = isset($order->orderLines) && $order->orderLines->first()->id;

        return [
            'customer_id' => 1,
            'product_id' => 1,
            'order_id' => $orderId,
            'order_line_id' => $orderLineId,
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->name(),
            'is_anonymous' => true,
        ];
    }
}
