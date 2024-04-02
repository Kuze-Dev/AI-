<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Database\Factories;

use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\ServiceOrder\Models\ServiceBill>
 */
class ServiceBillFactory extends Factory
{
    protected $model = ServiceBill::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'service_order_id' => ServiceOrderFactory::new(),
            'reference' => $this->faker->uuid(),
            'bill_date' => now(),
            'due_date' => now()->addDays(2),
            'currency' => $this->faker->currencyCode(),
            'service_price' => $this->faker->randomFloat(2, 1, 100),
            'additional_charges' => [],
            'total_amount' => $this->faker->randomFloat(2, 1, 100),
            'status' => ServiceBillStatus::PENDING,
            'sub_total' => $this->faker->randomFloat(2, 1, 100),
            'tax_percentage' => $this->faker->randomFloat(2, 1, 100),
            'tax_total' => $this->faker->randomFloat(2, 1, 100),
        ];
    }

    public function paid(): self
    {
        return $this->state(['status' => ServiceBillStatus::PAID]);
    }

    public function pending(): self
    {
        return $this->state(['status' => ServiceBillStatus::PENDING]);
    }

    public function billingDate(?Carbon $bill_date): self
    {
        return $this->state(['bill_date' => $bill_date]);
    }

    public function dueDate(?Carbon $due_date): self
    {
        return $this->state(['due_date' => $due_date]);
    }

    public function initial(): self
    {
        return $this->state([
            'bill_date' => null,
            'due_date' => null,
        ]);
    }
}
