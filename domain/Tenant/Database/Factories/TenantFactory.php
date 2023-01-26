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

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }

    /** @param string|array<string> $domains */
    public function withDomains(string|array $domains = null): self
    {
        return $this->afterCreating(function (Tenant $tenant) use ($domains) {
            foreach (Arr::wrap($domains ?? [fake()->domainName()]) as $domain) {
                $tenant->createDomain($domain);
            }
        });
    }
}
