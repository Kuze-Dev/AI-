<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('site.front_end_domain', 'example.com');
        $this->migrator->add('cms.front_end_domain');
    }
};
