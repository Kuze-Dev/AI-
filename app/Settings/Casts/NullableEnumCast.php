<?php

declare(strict_types=1);

namespace App\Settings\Casts;

use BackedEnum;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;
use UnitEnum;

class NullableEnumCast implements SettingsCast
{
    private string $enum;

    public function __construct(string $enum)
    {
        $this->enum = $enum;
    }

    /** @param string|int|null $payload */
    public function get($payload): ?UnitEnum
    {
        if (is_a($this->enum, BackedEnum::class, true)) {
            return $this->enum::tryFrom($payload ?? '');
        }

        if (is_a($this->enum, UnitEnum::class, true)) {
            foreach ($this->enum::cases() as $enum) {
                if ($enum->name === $payload) {
                    return $enum;
                }
            }
        }

        return null;
    }

    /** @param UnitEnum|null $payload */
    public function set($payload): string|int|null
    {
        if ($payload instanceof BackedEnum) {
            return $payload->value;
        }

        if ($payload instanceof UnitEnum) {
            return $payload->name;
        }

        return $payload;
    }
}
