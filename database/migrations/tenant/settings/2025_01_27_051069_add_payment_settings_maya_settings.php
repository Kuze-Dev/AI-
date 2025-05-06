<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('payments.maya_publishable_key', null);
        $this->migrator->add('payments.maya_secret_key', null);
        $this->migrator->add('payments.maya_production_mode', false);
    }
};
