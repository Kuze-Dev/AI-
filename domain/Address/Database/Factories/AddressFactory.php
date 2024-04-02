<?php

declare(strict_types=1);

namespace Domain\Address\Database\Factories;

use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Address;
use Domain\Customer\Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Address\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'customer_id' => CustomerFactory::new(),
            'state_id' => StateFactory::new(),
            'address_line_1' => $this->faker->address(),
            'zip_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'is_default_shipping' => $this->faker->boolean(),
            'is_default_billing' => $this->faker->boolean(),
            'label_as' => Arr::random(AddressLabelAs::cases())->value,
        ];
    }

    public function defaultShipping(bool $state = true): self
    {
        return $this->state(['is_default_shipping' => $state]);
    }

    public function defaultBilling(bool $state = true): self
    {
        return $this->state(['is_default_billing' => $state]);
    }
}
