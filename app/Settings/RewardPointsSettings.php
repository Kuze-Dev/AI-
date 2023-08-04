<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class RewardPointsSettings extends Settings
{
    public ?int $minimum_amount = 0;

    public ?int $equivalent_point = 0;

    public static function group(): string
    {
        return 'reward-points';
    }
}
