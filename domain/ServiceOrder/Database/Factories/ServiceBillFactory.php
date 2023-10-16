<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Database\Factories;

use Carbon\Carbon;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\ServiceOrder\Models\ServiceBill>
 */
class ServiceBillFactory extends Factory
{
    protected $model = ServiceBill::class;

    public function definition(): array
    {
        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = ServiceOrderFactory::new()->createOne();

        return [
            'service_order_id' => $serviceOrder->id,
            'reference' => $serviceOrder->reference,
            'bill_date' => now(),
            'due_date' => now()->addDays(2),
            'service_price' => $this->faker->randomFloat(2, 1, 100),
            'additional_charges' => [],
            'total_amount' => $this->faker->randomFloat(2, 1, 100),
            'status' => ServiceBillStatus::PENDING,
            'email_notification_sent_at' => null,
        ];
    }

    public function paid(): self
    {
        return $this->state(['status' => ServiceBillStatus::PAID]);
    }

    /** TODO: to be removed */
    public function unpaid(): self
    {
        return $this->state(['status' => ServiceBillStatus::FORPAYMENT]);
    }

    public function forPayment(): self
    {
        return $this->state(['status' => ServiceBillStatus::FORPAYMENT]);
    }

    public function billingDate(Carbon $bill_date): self
    {
        return $this->state(['bill_date' => $bill_date]);
    }
}
