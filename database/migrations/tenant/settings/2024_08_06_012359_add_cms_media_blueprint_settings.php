<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('cms.media_blueprint_id');
    }

    public function down(): void
    {
        $this->migrator->delete('cms.media_blueprint_id');
    }
};
