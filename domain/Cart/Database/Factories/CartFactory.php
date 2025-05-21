<?php

declare(strict_types=1);

namespace Domain\Cart\Database\Factories;

use Domain\Cart\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Cart\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'customer_id' => 1,
            'coupon_code' => null,
        ];
    }

    public function setCustomerId(int $id): self
    {
        return $this->state(['customer_id' => $id]);
    }

    public function setGuestId(string $sessionId): self
    {
        return $this->state([
            'customer_id' => null,
            'session_id' => $sessionId,
        ]);
    }
}
