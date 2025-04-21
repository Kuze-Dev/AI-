<?php

declare(strict_types=1);

namespace Domain\Taxation\Database\Factories;

use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Enums\TaxZoneType;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Taxation\Models\TaxZone>
 */
class TaxZoneFactory extends Factory
{
    protected $model = TaxZone::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'price_display' => Arr::random(PriceDisplay::cases()),
            'is_active' => false,
            'is_default' => false,
            'type' => Arr::random(TaxZoneType::cases()),
            'percentage' => $this->faker->randomFloat(3, max: 100.00),
        ];
    }

    public function isDefault(bool $state = true): self
    {
        return $this->state(['is_default' => $state]);
    }
}
