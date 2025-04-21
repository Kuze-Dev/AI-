<?php

declare(strict_types=1);

namespace Domain\Cart\Database\Factories;

use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Cart\Models\CartLine>
 */
class CartLineFactory extends Factory
{
    protected $model = CartLine::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'cart_id' => 1,
            'purchasable_id' => 1,
            'purchasable_type' => Product::class,
            'quantity' => 1,
            'remarks' => 'test remarks',
        ];
    }

    public function setCartId(int $id): self
    {
        return $this->state(['cart_id' => $id]);
    }

    public function setPurchasableId(int $id): self
    {
        return $this->state(['purchasable_id' => $id]);
    }

    public function setPurchasableType(string $type): self
    {
        return $this->state(['purchasable_type' => $type]);
    }
}
