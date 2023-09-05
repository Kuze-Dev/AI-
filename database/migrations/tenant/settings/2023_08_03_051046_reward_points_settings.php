<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('reward-points.minimum_amount', 100);
        $this->migrator->add('reward-points.equivalent_point', 1);

    }
};
