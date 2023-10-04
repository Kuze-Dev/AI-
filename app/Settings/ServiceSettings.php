<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ServiceSettings extends Settings
{
    public ?int $service_category;

    // admin email notif
    public bool $admin_should_receive = false;
    public string $admin_main_receiver = '';
    public ?array $admin_cc = [];
    public ?array $admin_bcc = [];

    //customer email notif
    public string $email_sender_name = '';
    public ?array $email_reply_to = [];
    public ?string $email_footer = null;

    public static function group(): string
    {
        return 'service';
    }
}
