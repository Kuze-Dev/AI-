<?php

declare(strict_types=1);

namespace Domain\Tier\Database\Factories;

use Domain\Tier\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Tier\Models\Tier>
 */
class TierFactory extends Factory
{
    protected $model = Tier::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];
    }

    public function deleted(): self
    {
        return $this->state(['deleted_at' => now()]);
    }

    public static function createDefault(): Tier
    {
        return self::new()->createOne(['name' => config('domain.tier.default')]);
    }

    public static function createWholesaler(): Tier
    {
        return self::new()->createOne(['name' => config('domain.tier.wholesaler-domestic')]);
    }
}
