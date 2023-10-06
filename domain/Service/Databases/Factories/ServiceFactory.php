<?php

declare(strict_types=1);

namespace Domain\Service\Databases\Factories;

use Carbon\Carbon;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Service\Enums\BillingCycle;
use Domain\Service\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $random = [true, false];
        $billing = [];
        foreach (BillingCycle::cases() as $billingCycle) {
            $billing[$billingCycle->value] = $billingCycle->name;
        }
        $days = [];
        for ($day = 1; $day <= Carbon::now()->daysInMonth; $day++) {
            $days[] = $day;
        }

        return [
//            'blueprint_id' => Blueprint::query()->pluck('id')->random(1)->first(),
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'retail_price' => $this->faker->numberBetween(1, 99999),
            'selling_price' => $this->faker->numberBetween(1, 99999),
            'billing_cycle' => array_rand($billing),
            'due_date_every' => array_rand($days),
            'pay_upfront' => array_rand($random),
            'is_featured' => array_rand($random),
            'is_special_offer' => array_rand($random),
            'is_subscription' => array_rand($random),
            'status' => array_rand(['active', 'inactive']),
        ];
    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }
}
