<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CMSSettings extends Settings
{
    public ?string $deploy_hook;
    public ?string $front_end_preview_page_url = null;
    public ?string $domain = null;

    public static function group(): string
    {
        return 'cms';
    }
}
