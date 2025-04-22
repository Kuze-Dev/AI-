<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\State;
use Illuminate\Support\Arr;
use Worksome\RequestFactories\RequestFactory;

class AddressRequestFactory extends RequestFactory
{
    #[\Override]
    public function definition(): array
    {
        return [
            'country_id' => 1,
            'state_id' => 1,
            'address_line_1' => $this->faker->address(),
            'zip_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'label_as' => Arr::random(AddressLabelAs::cases())->value,
        ];
    }

    public function withState(State $state): self
    {
        return $this->state([
            'country_id' => $state->country->getRouteKey(),
            'state_id' => $state->getRouteKey(),
        ]);
    }

    public function defaultShipping(?bool $state = true): self
    {
        return $this->state([
            'is_default_shipping' => $state,
        ]);
    }

    public function defaultBilling(?bool $state = true): self
    {
        return $this->state([
            'is_default_billing' => $state,
        ]);
    }
}
