<?php

declare(strict_types=1);

namespace Domain\Role\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Spatie\Permission\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'guard_name' => Guard::getDefaultName(Role::class),
        ];
    }

    public function guard(string $guard): self
    {
        return $this->state(['guard_name' => $guard]);
    }
}
