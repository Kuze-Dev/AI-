<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Database\Factories;

use Domain\Service\Enums\BillingCycle;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\ServiceOrder\Models\ServiceOrder>
 */
class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    public function definition(): array
    {
        return [
            'service_id' => $this->faker->randomNumber(),
            'customer_id' => $this->faker->randomNumber(),
            'admin_id' => $this->faker->randomNumber(),
            'reference' => $this->faker->uuid(),
            'customer_first_name' => $this->faker->firstName(),
            'customer_last_name' => $this->faker->lastName(),
            'customer_email' => $this->faker->email(),
            'customer_mobile' => $this->faker->phoneNumber(),
            'customer_form' => [],
            'additional_charges' => [],
            'currency_code' => $this->faker->currencyCode(),
            'currency_name' => $this->faker->name(),
            'currency_symbol' => $this->faker->randomLetter(),
            'service_name' => $this->faker->name(),
            'service_price' => $this->faker->randomFloat(2, 1, 100),
            'billing_cycle' => BillingCycle::MONTHLY,
            'due_date_every' => $this->faker->randomDigit(),
            'schedule' => now()->addDay(),
            'status' => ServiceOrderStatus::PENDING,
            'cancelled_reason' => null,
            'total_price' => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
