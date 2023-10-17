<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Database\Factories;

use Carbon\Carbon;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Database\Factories\PaymentFactory;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\ServiceOrder\Models\ServiceTransaction>
 */
class ServiceTransactionFactory extends Factory
{
    protected $model = ServiceTransaction::class;

    public function definition(): array
    {
        return [
            'service_order_id' => ServiceOrderFactory::new(),
            'service_bill_id' => ServiceBillFactory::new(),
            'payment_method_id' => PaymentMethodFactory::new(),
            'currency' => $this->faker->currencyCode(),
            'total_amount' => $this->faker->randomNumber(1, 100),
            'status' => ServiceTransactionStatus::PAID,
        ];
    }
}
