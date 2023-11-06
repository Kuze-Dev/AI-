<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('site.name', 'Tenant Application');
        $this->migrator->add('site.description', 'Welcome to your application!');
        $this->migrator->add('site.author', 'Tenant');
        $this->migrator->add('site.logo', '');
        $this->migrator->add('site.favicon', '');
    }
};
