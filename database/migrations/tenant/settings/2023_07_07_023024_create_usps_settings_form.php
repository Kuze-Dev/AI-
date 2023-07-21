<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('shipping.usps_production_mode', false);
        $this->migrator->addEncrypted('shipping.usps_credentials');

        $this->migrator->add('shipping.usp_production_mode', false);
        $this->migrator->addEncrypted('shipping.usp_credentials');

    }
};
