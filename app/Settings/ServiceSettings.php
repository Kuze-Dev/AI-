<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ServiceSettings extends Settings
{
    public ?int $service_category;

    public ?int $days_before_due_date_notification = 1;

    public static function group(): string
    {
        return 'service';
    }
}
