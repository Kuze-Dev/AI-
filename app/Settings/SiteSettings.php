<?php

declare(strict_types=1);

namespace App\Settings;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $name;

    public string $description;

    public string $author;

    public string $logo;

    public string $favicon;

    public string $front_end_domain;

    #[\Override]
    public static function group(): string
    {
        return 'site';
    }

    public function domainWithScheme(): string
    {
        return Request::getScheme().'://'.$this->front_end_domain;
    }

    public function getLogoUrl(): string
    {
        if (blank($this->logo)) {
            return '';
        }

        return Storage::disk(config('filament.default_filesystem_disk'))
            ->url($this->logo);
    }
}
