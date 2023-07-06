<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('payments.paypal_credentials', null);
        $this->migrator->add('payments.paypal_mode', false);
        $this->migrator->add('payments.stripe_credentials', null);
        $this->migrator->add('payments.stripe_mode', false);
    }
};
