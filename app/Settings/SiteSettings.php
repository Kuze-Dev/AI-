<?php

declare(strict_types=1);

namespace App\Settings;

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

    public static function group(): string
    {
        return 'site';
    }

    public function domainWithScheme(): string
    {
        /** @phpstan-ignore-next-line Cannot access offset 'scheme' on array{scheme?: string, host?: string, port?: int<0, 65535>, user?: string, pass?: string, path?: string,
         * query?: string, fragment?: string}|false. */
        $scheme = parse_url(config('app.url'))['scheme'];

        return $scheme.'://'.$this->front_end_domain;
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
