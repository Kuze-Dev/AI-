<?php

declare(strict_types=1);

namespace Domain\Site\Database\Factories;

use Domain\Site\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Site\Models\Site>
 */
class SiteFactory extends Factory
{
    protected $model = Site::class;

    #[\Override]
    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'name' => $name,
            'domain' => $this->faker->url(),
        ];
    }
}
