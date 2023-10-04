<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('service.service_category');
        $this->migrator->add('service.admin_should_receive', false);
        $this->migrator->add('service.admin_main_receiver', '');
        $this->migrator->add('service.admin_cc', null);
        $this->migrator->add('service.admin_bcc', null);
        $this->migrator->add('service.email_sender_name', '');
        $this->migrator->add('service.email_reply_to', null);
        $this->migrator->add('service.email_footer', null);
    }
};
