<?php

declare(strict_types=1);

namespace Domain\Role\Database\Factories;

use Domain\Role\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Spatie\Permission\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }

    public function guard(string $guard): self
    {
        return $this->state(['guard_name' => $guard]);
    }
}
