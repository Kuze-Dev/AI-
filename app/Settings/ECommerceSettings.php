<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ECommerceSettings extends Settings
{
    public ?string $front_end_domain = null;

    public static function group(): string
    {
        return 'e-commerce';
    }

    public function domainWithScheme(): ?string
    {
        if ($this->front_end_domain === null) {
            return null;
        }

        /** @phpstan-ignore-next-line Cannot access offset 'scheme' on array{scheme?: string, host?: string, port?: int<0, 65535>, user?: string, pass?: string, path?: string,
         * query?: string, fragment?: string}|false. */
        $scheme = parse_url(config('app.url'))['scheme'];

        return $scheme.'://'.$this->front_end_domain;
    }
}
