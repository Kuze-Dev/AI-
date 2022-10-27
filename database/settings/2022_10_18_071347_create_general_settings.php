<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('site.site_name', config('app.name'));
        $this->migrator->add('site.site_description', 'We will be using TALL (Tailwind, Alpine.JS, Livewire, and Laravel) stack to this new boilerplate.');
        $this->migrator->add('site.site_author', 'Halcyon AGILE');
        $this->migrator->add('site.site_logo', '');
        $this->migrator->add('site.site_favicon', '');
    }
};
