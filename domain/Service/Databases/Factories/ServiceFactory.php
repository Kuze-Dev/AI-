<?php

namespace Domain\Service\Databases\Factories;

use Domain\Blueprint\Models\Blueprint;
use Domain\Service\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model>
 */
class ServiceFactory extends Factory
{
    /**
     * @var class-string</Illuminate/Database/Eloquent/Model>
     */
    protected $model = Service::class;

    public function definition(): array
    {
        $random = [true, false];
        return [
            'blueprint_id' => Blueprint::query()->pluck('id')->random(1)->first(),
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'price' => $this->faker->numberBetween(1,99999),
            'is_featured' => array_rand($random),
            'is_special_offer' => array_rand($random),
            'is_subscription' => array_rand($random),
            'status' => array_rand(['active', 'inactive']),
        ];
    }
}
