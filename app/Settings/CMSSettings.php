<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CMSSettings extends Settings
{
    public ?string $deploy_hook;
    public ?string $signed_route;

    public static function group(): string
    {
        return 'cms';
    }
}
