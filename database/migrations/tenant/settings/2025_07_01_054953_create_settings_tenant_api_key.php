<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrator->add('api.api_key', null);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->migrator->delete('api.api_key');
    }
};
