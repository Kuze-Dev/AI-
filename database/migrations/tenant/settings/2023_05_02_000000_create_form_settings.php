<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('form.provider', null);
        $this->migrator->add('form.site_key', null);
        $this->migrator->add('form.secret_key', null);
    }
};
