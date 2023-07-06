<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Domain\Address\Models\State;
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

    public function shippingAddress(State $state): self
    {
        return $this->state([
            'shipping_state_id' => $state->getRouteKey(),
            'shipping_address_line_1' => $this->faker->address(),
            'shipping_zip_code' => $this->faker->postcode(),
            'shipping_city' => $this->faker->city(),
            'shipping_label_as' => 'home',
        ]);
    }

    public function billingSameAsShipping(): self
    {
        return $this->state([
            'billing_same_as_shipping' => true,
        ]);
    }

    public function billingAddress(State $state): self
    {
        return $this->state([
            'billing_same_as_shipping' => false,
            'billing_state_id' => $state->getRouteKey(),
            'billing_address_line_1' => $this->faker->address(),
            'billing_zip_code' => $this->faker->postcode(),
            'billing_city' => $this->faker->city(),
            'billing_label_as' => 'home',
        ]);
    }
}
