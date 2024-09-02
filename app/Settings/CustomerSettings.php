<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CustomerSettings extends Settings
{
    public ?string $blueprint_id = '';

    public ?string $date_format = '';

    public ?string $customer_register_invitation_greetings = '';

    public ?string $customer_register_invitation_body = '';

    public ?string $customer_register_invitation_salutation = '';

    public ?array $customer_email_notifications = [];

    public static function group(): string
    {
        return 'customer';
    }
}
