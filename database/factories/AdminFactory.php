<?php

namespace Database\Factories;

use Domain\Admin\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Admin\Models\Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => function (array $attributes) {
                $firstName = Str::of($attributes['first_name'])->camel();
                $lastName = Str::of($attributes['last_name'])->camel();
                $domain = parse_url(config('app.url'), PHP_URL_HOST);

                return "{$firstName}.{$lastName}@{$domain}";
            },
            'email_verified_at' => now(),
            'password' => 'secret',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): self
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function passwordPrompt(Command $command): self
    {
        return $this->state([
            'password' => function (array $attributes) use ($command) {
                return $command->secret("Enter a password for {$attributes['email']} (Keep this somewhere safe, you wont be able to see this again)");
            },
        ]);
    }
}
