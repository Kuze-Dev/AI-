<?php

declare(strict_types=1);

namespace Domain\Tenant\Database\Factories;

use Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Tenant\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'is_suspended' => false,
        ];
    }

    public function suspendedTenant(bool $isFeatured = true): self
    {
        return $this->state([
            'is_suspended' => $isFeatured,
        ]);
    }

    /** @param  string|array<string>  $domains */
    public function withDomains(string|array|null $domains = null): self
    {
        return $this->afterCreating(function (Tenant $tenant) use ($domains) {
            foreach (Arr::wrap($domains ?? [fake()->domainName()]) as $domain) {
                $tenant->createDomain($domain);
            }
        });
    }

    public function withDatabase(): self
    {
        return $this->state([
            Tenant::internalPrefix().'db_host' => 'test',
            Tenant::internalPrefix().'db_port' => '3306 ',
            Tenant::internalPrefix().'db_name' => 'test',
            Tenant::internalPrefix().'db_username' => 'test',
            Tenant::internalPrefix().'db_password' => 'test',
        ]);
    }
}
