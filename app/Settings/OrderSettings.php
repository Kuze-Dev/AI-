<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class OrderSettings extends Settings
{
    //email
    public ?string $email_sender_name = null;
    public ?array $email_reply_to = [];
    public ?string $email_footer = null;

    public static function group(): string
    {
        return 'order';
    }
}
