<?php

declare(strict_types=1);

namespace App\Settings;

use Illuminate\Support\Facades\Request;
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

        return Request::getScheme().'://'.$this->front_end_domain;
    }
}
