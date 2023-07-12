<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $name;

    public string $description;

    public string $author;

    public string $logo;

    public string $favicon;

    public string $front_end_domain;

    public static function group(): string
    {
        return 'site';
    }
}
