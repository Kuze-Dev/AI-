<?php

declare(strict_types=1);

namespace Domain\Internationalization\Database\Factories;

use Domain\Internationalization\Models\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Internationalization\Models\Locale>
 */
class LocaleFactory extends Factory
{
    protected $model = Locale::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->languageCode,
            'name' => $this->faker->unique()->languageCode,
            'is_default' => false,
        ];
    }

    public static function createDefault(): Locale
    {
        $locale = Locale::whereCode('en')->first();

        if (! $locale) {
            return self::new([
                'name' => 'English (en)',
                'code' => 'en',
                'is_default' => true,
            ])->createOne();
        }

        return $locale;
    }
}
