<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ServiceSettings extends Settings
{
    public ?int $service_category;

    public ?string $domain_path_segment;

    // admin email notif
    public bool $admin_should_receive = false;

    public string $admin_main_receiver = '';

    public ?array $admin_cc = [];

    public ?array $admin_bcc = [];

    //customer email notif
    public string $email_sender_name = '';

    public ?array $email_reply_to = [];

    public ?string $email_footer = null;

    public ?int $days_before_due_date_notification = 1;

    #[\Override]
    public static function group(): string
    {
        return 'service';
    }
}
