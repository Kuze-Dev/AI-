<?php

declare(strict_types=1);

namespace App\Settings\Subscriber;

use App\Settings\Support\SettingsCacheFactory;

class SettingsEventSubscriber extends \Spatie\LaravelSettings\SettingsEventSubscriber
{
    public function __construct(
        private readonly SettingsCacheFactory $settingsCacheFactory
    ) {
        parent::__construct($this->settingsCacheFactory);
    }
}
