<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class () extends SettingsMigration {
    public function up(): void
    {
        // $this->migrator->add('payments.paypal_credentials', null);

        $this->migrator->add('payments.paypal_secret_id', null);
        $this->migrator->add('payments.paypal_secret_key', null);

        // $this->migrator->add('payments.paypal_mode', false);
        $this->migrator->add('payments.paypal_production_mode', false);

        // $this->migrator->add('payments.stripe_credentials', null);

        $this->migrator->add('payments.stripe_publishable_key', null);
        $this->migrator->add('payments.stripe_secret_key', null);

        $this->migrator->add('payments.stripe_production_mode', false);
    }
};
