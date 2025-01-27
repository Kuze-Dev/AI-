<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {

        $this->migrator->add('payments.vision_pay_apiKey', null);

        $this->migrator->add('payments.vision_pay_production_mode', false);
   
    }

    public function down(): void
    {
        $this->migrator->delete('payments.vision_pay_apiKey');

        $this->migrator->delete('payments.vision_pay_production_mode');

       
    }
};
