<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class RewardPointsSettings extends Settings
{
    public ?int $minimum_amount = 100;

    public ?int $equivalent_point = 1;

    #[\Override]
    public static function group(): string
    {
        return 'reward-points';
    }
}
