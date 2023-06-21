<?php

declare(strict_types=1);

namespace Domain\Customer\Database\Factories;

use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Customer\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
