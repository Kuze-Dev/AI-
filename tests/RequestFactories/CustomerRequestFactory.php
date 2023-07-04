<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Illuminate\Support\Str;
use Worksome\RequestFactories\RequestFactory;

class CustomerRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => function (array $attributes) {
                $firstName = Str::of($attributes['first_name'])->camel();
                $lastName = Str::of($attributes['last_name'])->camel();

                return "{$firstName}.{$lastName}@fake.com";
            },
            'password' => 'secret',
            'mobile' => $this->faker->phoneNumber(),
            'birth_date' => now()->subYears($this->faker->randomDigitNotNull())->format('Y-m-d'),
        ];
    }
}
