<?php

declare(strict_types=1);

namespace Domain\Customer\Database\Factories;

use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Database\Factories\TierFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Customer\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'tier_id' => TierFactory::new(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->faker->password(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'mobile' => $this->faker->phoneNumber(),
            'status' => Arr::random(Status::cases()),
            'birth_date' => now()->subYears($this->faker->randomDigitNotNull()),
        ];
    }
}
