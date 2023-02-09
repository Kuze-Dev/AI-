<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;
use Spatie\LaravelSettings\SettingsContainer;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can get settings', function () {
    foreach (
        app(SettingsContainer::class)->getSettingClasses()
        as $settings
    ) {
        $setting = app($settings);

        getJson('api/settings/'.$setting::group())
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($setting) {
                $json
                    ->where('data.id', $setting::group())
                    ->where('data.type', 'settings')
                    ->count('data.attributes', count($setting->toArray()))
                    ->etc();
            });
    }
});
