<?php

declare(strict_types=1);

namespace Domain\Cart\Database\Factories;

use Domain\Cart\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Cart\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'customer_id' => 1,
            'coupon_code' => null,
        ];
    }

    public function setCustomerId(int $id): self
    {
        return $this->state(['customer_id' => $id]);
    }
}
