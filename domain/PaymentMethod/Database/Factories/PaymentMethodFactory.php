<?php

declare(strict_types=1);

namespace Domain\PaymentMethod\Database\Factories;

use Domain\PaymentMethod\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\PaymentMethod\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /** Specify reference model. */
    protected $model = PaymentMethod::class;

    /** Define values of model instance. */
    #[\Override]
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->word(),
            'subtitle' => $this->faker->unique()->word(),
            'gateway' => 'manual',
            'description' => 'test payment method',
        ];
    }
}
