<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('customer.customer_register_invitation_greetings', '<b>Hello</b>');

        $this->migrator->add('customer.customer_register_invitation_body', '<p>Welcome to :site! We\'re thrilled to have you on board. If you have any questions or need assistance, feel free to reach out. We\'re here to make your experience with us exceptional.</p>');

        $this->migrator->add('customer.customer_register_invitation_salutation', '<p>Regards,<br>'.config('app.name').'</p>');
    }

    public function down(): void
    {
        $this->migrator->delete('customer.customer_register_invitation_greetings');

        $this->migrator->delete('customer.customer_register_invitation_body');

        $this->migrator->delete('customer.customer_register_invitation_salutation');
    }
};
