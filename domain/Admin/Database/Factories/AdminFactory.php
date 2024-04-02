<?php

declare(strict_types=1);

namespace Domain\Admin\Database\Factories;

use Domain\Admin\Models\Admin;
use Domain\Auth\Actions\EnableTwoFactorAuthenticationAction;
use Domain\Auth\Actions\GenerateRecoveryCodesAction;
use Domain\Auth\Actions\SetupTwoFactorAuthenticationAction;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Admin\Models\Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    #[\Override]
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
            'active' => true,
            'email_verified_at' => now(),
            'password' => 'secret',
            'remember_token' => Str::random(10),
            'timezone' => config('domain.admin.default_timezone'),
        ];
    }

    public function unverified(): self
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function passwordPrompt(Command $command): self
    {
        return $this->state([
            'password' => function (array $attributes) use ($command) {
                return $command->secret("Enter a password for {$attributes['email']} (Keep this somewhere safe, you wont be able to see this again)");
            },
        ]);
    }

    public function active(bool $active = true): self
    {
        return $this->state(['active' => $active]);
    }

    public function softDeleted(): self
    {
        return $this->state(['deleted_at' => now()]);
    }

    public function withTwoFactorEnabled(): self
    {
        return $this->afterCreating(function (Admin $admin) {
            app(SetupTwoFactorAuthenticationAction::class)->execute($admin);

            if (! $admin->twoFactorAuthentication?->secret) {
                return;
            }

            $code = app(TwoFactorAuthenticationProvider::class)->getCurrentOtp($admin->twoFactorAuthentication->secret);

            app(EnableTwoFactorAuthenticationAction::class)->execute($admin, $code);
            app(GenerateRecoveryCodesAction::class)->execute($admin);
        });
    }
}
