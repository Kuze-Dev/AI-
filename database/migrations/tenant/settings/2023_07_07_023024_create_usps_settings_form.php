<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('shipping.usps_credentials', null);
        $this->migrator->add('shipping.usps_mode', false);
        $this->migrator->add('shipping.usp_credentials', null);
        $this->migrator->add('shipping.usp_mode', false);

    }
};
