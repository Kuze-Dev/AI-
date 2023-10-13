<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Database\Factories;

use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceBillFactory extends Factory
{
    protected $model = ServiceBill::class;

    public function definition(): array
    {
        $serviceOrder = ServiceOrderFactory::new()->make();

        return [
            'service_order_id' => $serviceOrder->id,
            'reference' => $serviceOrder->reference,
            'bill_date' => now()->addDay(),
            'due_date' => now()->addDays(2),
            'service_price' => $this->faker->randomFloat(2, 1, 100),
            'additional_charges' => [],
            'total_amount' => $this->faker->randomFloat(2, 1, 100),
            'status' => ServiceBillStatus::PENDING,
        ];
    }

    public function paid(): self
    {
        return $this->state(['status' => ServiceBillStatus::PAID]);
    }
}
