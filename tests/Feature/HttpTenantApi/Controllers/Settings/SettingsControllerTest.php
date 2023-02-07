<?php

declare(strict_types=1);

use Spatie\LaravelSettings\SettingsContainer;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can get settings', function () {
    foreach (
        app(SettingsContainer::class)
            ->getSettingClasses()
            ->map(fn (string $setting): string => app($setting)::group())
        as $group
    ) {
        getJson('api/settings/'.$group)
            ->assertOk();
    }
});
