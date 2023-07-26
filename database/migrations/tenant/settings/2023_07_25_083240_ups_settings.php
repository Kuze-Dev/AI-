<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('shipping.ups_production_mode', false);
        $this->migrator->addEncrypted('shipping.access_license_number');
        $this->migrator->addEncrypted('shipping.ups_username');
        $this->migrator->addEncrypted('shipping.ups_password');
    }
};
