<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\State;
use Domain\Customer\Enums\Gender;
use Domain\Tier\Models\Tier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Worksome\RequestFactories\RequestFactory;

class CustomerRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'image' => UploadedFile::fake()->image('test_image.jpg'),
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
            'mobile' => $this->faker->numerify('###########'),
            'birth_date' => now()->subYears($this->faker->randomDigitNotNull())->format('Y-m-d'),
        ];
    }

    public function withTier(Tier $tier): self
    {
        return $this->state([
            'tier_id' => $tier->getKey(),
        ]);
    }

    public function withShippingAddress(State $state): self
    {
        return $this->state([
            'shipping_country_id' => $state->country->getKey(),
            'shipping_state_id' => $state->getKey(),
            'shipping_address_line_1' => $this->faker->address(),
            'shipping_zip_code' => $this->faker->postcode(),
            'shipping_city' => $this->faker->city(),
            'shipping_label_as' => Arr::random(AddressLabelAs::cases())->value,
        ]);
    }

    public function withBillingSameAsShipping(): self
    {
        return $this->state([
            'same_as_shipping' => true,
        ]);
    }

    public function withBillingAddress(State $state): self
    {
        return $this->state([
            'same_as_shipping' => false,
            'billing_country_id' => $state->country->getKey(),
            'billing_state_id' => $state->getKey(),
            'billing_address_line_1' => $this->faker->address(),
            'billing_zip_code' => $this->faker->postcode(),
            'billing_city' => $this->faker->city(),
            'billing_label_as' => Arr::random(AddressLabelAs::cases())->value,
        ]);
    }
}
