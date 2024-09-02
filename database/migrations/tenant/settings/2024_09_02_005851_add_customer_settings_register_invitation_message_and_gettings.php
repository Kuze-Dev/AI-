<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('customer.customer_register_invation_greetings', 'Hello');

        $this->migrator->add('customer.customer_register_invation_body', 'Welcome to :site! We\'re thrilled to have you on board. If you have any questions or need assistance, feel free to reach out. We\'re here to make your experience with us exceptional.');
    }

    public function down(): void
    {
        $this->migrator->delete('customer.customer_register_invation_greetings');

        $this->migrator->delete('customer.customer_register_invation_body');
    }
};
