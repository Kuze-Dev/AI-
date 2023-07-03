<?php

declare(strict_types=1);

namespace Domain\Support\Payments\Database\Factories;

use Domain\Support\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Support\Payments\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /** Specify reference model. */
    protected $model = Payment::class;

    /** Define values of model instance. */
    public function definition(): array
    {
        return [
            'payable_type' => 'order',
            'payable_id' => 1,
            'payment_method_id' => '',
            'gateway' => 'paypal',
            'currency' => 'PHP',
            'amount' => '1000',
            'status' => 'pending',
            'payment_details' => ['subtotal' => '1000'],
        ];
    }

    public function setPaymentMethod(int $id): self
    {

        return $this->state(['payment_method_id' => $id]);
    }

    public function setGateway(string $gateway): self
    {

        return $this->state(['gateway' => $gateway]);
    }
}
