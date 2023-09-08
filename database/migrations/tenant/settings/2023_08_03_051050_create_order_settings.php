<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('order.email_sender_name', null);
        $this->migrator->add('order.email_reply_to', null);
        $this->migrator->add('order.email_footer', null);
    }
};
