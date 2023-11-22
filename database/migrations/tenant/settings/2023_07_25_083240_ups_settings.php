<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('shipping.ups_production_mode', false);
        $this->migrator->addEncrypted('shipping.ups_client_id');
        $this->migrator->addEncrypted('shipping.ups_client_secret');
        $this->migrator->addEncrypted('shipping.ups_shipper_account');

    }
};
