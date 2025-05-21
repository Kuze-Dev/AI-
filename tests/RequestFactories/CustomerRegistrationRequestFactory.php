<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\State;
use Domain\Customer\Enums\Gender;
use Domain\Tier\Models\Tier;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Worksome\RequestFactories\RequestFactory;

class CustomerRegistrationRequestFactory extends RequestFactory
{
    #[\Override]
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
            'gender' => Arr::random(Gender::cases())->value,
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'mobile' => $this->faker->phoneNumber(),
            'tier_id' => Tier::whereName(config('domain.tier.default'))->first()->getKey(),
            'birth_date' => now()->subYears($this->faker->randomDigitNotNull())->format('Y-m-d'),
        ];
    }

    public function withShippingAddress(State $state): self
    {
        return $this->state([
            'shipping' => [
                'country_id' => $state->country->getRouteKey(),
                'state_id' => $state->getRouteKey(),
                'address_line_1' => $this->faker->address(),
                'zip_code' => $this->faker->postcode(),
                'city' => $this->faker->city(),
                'label_as' => Arr::random(AddressLabelAs::cases())->value,
            ],
        ]);
    }

    public function withBillingSameAsShipping(): self
    {
        return $this->state([
            'billing' => [
                'same_as_shipping' => true,
            ],
        ]);
    }

    public function withBillingAddress(State $state): self
    {
        return $this->state([
            'billing' => [
                'same_as_shipping' => false,
                'country_id' => $state->country->getRouteKey(),
                'state_id' => $state->getRouteKey(),
                'address_line_1' => $this->faker->address(),
                'zip_code' => $this->faker->postcode(),
                'city' => $this->faker->city(),
                'label_as' => Arr::random(AddressLabelAs::cases())->value,
            ],
        ]);
    }
}
