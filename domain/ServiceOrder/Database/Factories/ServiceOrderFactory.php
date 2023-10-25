<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Database\Factories;

use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\Service\Enums\BillingCycleEnum;
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
            'service_id' => ServiceFactory::new()->withDummyBlueprint(),
            'customer_id' => CustomerFactory::new(),
            'admin_id' => AdminFactory::new(),
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
            'billing_cycle' => BillingCycleEnum::MONTHLY,
            'due_date_every' => $this->faker->randomDigit(),
            'schedule' => now()->addDay(),
            'status' => ServiceOrderStatus::PENDING,
            'cancelled_reason' => null,
            'total_price' => $this->faker->randomFloat(2, 1, 100),
            'sub_total' => $this->faker->randomFloat(2, 1, 100),
            'tax_percentage' => $this->faker->randomFloat(2, 1, 100),
            'tax_total' => $this->faker->randomFloat(2, 1, 100),
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => ServiceOrderStatus::ACTIVE]);
    }

    public function inactive(): self
    {
        return $this->state(['status' => ServiceOrderStatus::INACTIVE]);
    }

    public function closed(): self
    {
        return $this->state(['status' => ServiceOrderStatus::CLOSED]);
    }

    public function forPayment(): self
    {
        return $this->state(['status' => ServiceOrderStatus::FORPAYMENT]);
    }

    public function dailyBillingCycle(): self
    {
        return $this->state([
            'billing_cycle' => BillingCycleEnum::DAILY,
            'due_date_every' => null,
        ]);
    }
}
