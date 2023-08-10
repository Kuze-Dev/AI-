<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('site.name', config('app.name'));
        $this->migrator->add('site.description', 'We will be using TALL (Tailwind, Alpine.JS, Livewire, and Laravel) stack to this new boilerplate.');
        $this->migrator->add('site.author', 'Halcyon AGILE');
        $this->migrator->add('site.logo', 'logo.png');
        $this->migrator->add('site.favicon', 'favicon.png');
    }
};
