<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('site.site_name', 'Tenant Application');
        $this->migrator->add('site.site_description', 'Welcome to your application!');
        $this->migrator->add('site.site_author', 'Tenant');
        $this->migrator->add('site.site_logo', '');
        $this->migrator->add('site.site_favicon', '');
    }
};
