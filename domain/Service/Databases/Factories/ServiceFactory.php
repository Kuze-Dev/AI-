<?php

declare(strict_types=1);

namespace Domain\Service\Databases\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Service\Enums\BillingCycle;
use Domain\Service\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'retail_price' => $this->faker->numberBetween(1, 99_999),
            'selling_price' => $this->faker->numberBetween(1, 99_999),
            'billing_cycle' => Arr::random(BillingCycle::cases()),
            'due_date_every' => Arr::random(range(1, now()->daysInMonth)),
            'pay_upfront' => $this->faker->boolean(),
            'is_featured' => $this->faker->boolean(),
            'is_special_offer' => $this->faker->boolean(),
            'is_subscription' => $this->faker->boolean(),
            'status' => $this->faker->boolean(),
        ];
    }

    public function featured(): self
    {
        return $this->state([
            'is_featured' => true,
        ]);
    }

    public function specialOffer(): self
    {
        return $this->state([
            'is_special_offer' => true,
        ]);
    }

    public function subscription(): self
    {
        return $this->state([
            'is_subscription' => true,
        ]);
    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }
}
