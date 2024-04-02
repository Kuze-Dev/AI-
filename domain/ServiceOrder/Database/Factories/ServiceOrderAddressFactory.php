<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Database\Factories;

use Domain\Address\Enums\AddressLabelAs;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Domain\ServiceOrder\Models\ServiceOrderAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\ServiceOrder\Models\ServiceOrderAddress>
 */
class ServiceOrderAddressFactory extends Factory
{
    protected $model = ServiceOrderAddress::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'service_order_id' => ServiceOrderFactory::new(),
            'type' => ServiceOrderAddressType::SERVICE_ADDRESS,
            'country' => $this->faker->country(),
            'state' => $this->faker->streetName(),
            'label_as' => AddressLabelAs::HOME,
            'address_line_1' => $this->faker->streetAddress(),
            'zip_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
        ];
    }

    public function service(): self
    {
        return $this->state(['type' => ServiceOrderAddressType::SERVICE_ADDRESS]);
    }

    public function billing(): self
    {
        return $this->state(['type' => ServiceOrderAddressType::BILLING_ADDRESS]);
    }
}
