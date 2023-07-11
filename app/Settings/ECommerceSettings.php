<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ECommerceSettings extends Settings
{
    public ?string $domain = null;

    public static function group(): string
    {
        return 'e-commerce';
    }

    public function domainWithScheme(): ?string
    {
        if ($this->domain === null) {
            return null;
        }

        return parse_url(config('app.url'))['scheme'].'://'.$this->domain;
    }
}
