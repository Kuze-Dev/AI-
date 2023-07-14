<?php

declare(strict_types=1);

namespace Domain\Customer\Database\Factories;

use Carbon\Carbon;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Database\Factories\TierFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
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
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->faker->password(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'mobile' => $this->faker->phoneNumber(),
            'gender' => Arr::random(Gender::cases()),
            'status' => Arr::random(Status::cases()),
            'birth_date' => now()->subYears($this->faker->randomDigitNotNull()),
            'remember_token' => Str::random(10),
        ];
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
        return $this->state(['status' => Status::ACTIVE]);
    }

    public function inactive(): self
    {
        return $this->state(['status' => Status::INACTIVE]);
    }
}
