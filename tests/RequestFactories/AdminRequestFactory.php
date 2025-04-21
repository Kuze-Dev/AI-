<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Illuminate\Support\Str;
use Worksome\RequestFactories\RequestFactory;

class AdminRequestFactory extends RequestFactory
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
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'active' => true,
            'roles' => [],
            'permissions' => [],
        ];
    }

    public function roles(array $roles): self
    {
        return $this->state([
            'roles' => $roles,
        ]);
    }

    public function permissions(array $permissions): self
    {
        return $this->state([
            'permissions' => $permissions,
        ]);
    }

    public function active(bool $active = true): self
    {
        return $this->state([
            'active' => $active,
        ]);
    }
}
