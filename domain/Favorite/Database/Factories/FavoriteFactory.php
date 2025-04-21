<?php

declare(strict_types=1);

namespace Domain\Favorite\Database\Factories;

use Domain\Favorite\Models\Favorite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Favorite\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'customer_id' => 1,
            'product_id' => 1,
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
}
