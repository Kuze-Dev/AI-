<?php

declare(strict_types=1);

namespace Domain\Customer\Database\Factories;

use Domain\Address\Database\Factories\AddressFactory;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Database\Factories\TierFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
            'cuid' => $this->faker->unique()->uuid(),
            'email' => $this->faker->unique()->companyEmail(),
            'password' => $this->faker->password(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'mobile' => $this->faker->phoneNumber(),
            'gender' => Arr::random(Gender::cases()),
            'status' => Arr::random(Status::cases()),
            'register_status' => RegisterStatus::REGISTERED,
            'birth_date' => now()->subYears($this->faker->randomDigitNotNull()),
            'remember_token' => Str::random(10),
        ];
    }

    //    public function configure(): self
    //    {
    //        return $this
    //            ->afterCreating(function (Customer $customer) {
    //                if ($customer->addresses->isEmpty()) {
    //                    AddressFactory::new()
    //                        ->for($customer)
    //                        ->defaultShipping()
    //                        ->defaultBilling()
    //                        ->createOne();
    //                }
    //            });
    //    }

    public function hasAddress(): self
    {
        return $this->has(
            AddressFactory::new()
                ->defaultShipping()
                ->defaultBilling()
        );
    }

    public function deleted(): self
    {
        return $this->state(['deleted_at' => now()]);
    }

    public function verified(?Carbon $datetime = null): self
    {
        return $this->state(['email_verified_at' => $datetime ?? now()]);
    }

    public function unverified(): self
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function active(): self
    {
        return $this->status(Status::ACTIVE);
    }

    public function inactive(): self
    {
        return $this->status(Status::INACTIVE);
    }

    public function banned(): self
    {
        return $this->status(Status::BANNED);
    }

    public function status(Status $status): self
    {
        return $this->state(['status' => $status]);
    }

    public function registered(): self
    {
        return $this->state(['register_status' => RegisterStatus::REGISTERED]);
    }

    public function unregistered(): self
    {
        return $this->state(['register_status' => RegisterStatus::UNREGISTERED]);
    }

    public function withAddress(): self
    {
        return $this->has(AddressFactory::new());
    }
}
