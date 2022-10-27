<?php

declare(strict_types=1);

use App\Filament\Pages\Settings\Settings;
use Spatie\LaravelSettings\SettingsContainer;

use function Pest\Laravel\get;

beforeEach(fn () => loginAsAdmin());

it('can render page', function () {
    get(Settings::getUrl())
        ->assertOk();
});

it('can render page by groups', function () {
    foreach (
        app(SettingsContainer::class)
            ->getSettingClasses()
            ->map(fn (string $setting): string => app($setting)::group())
        as $group
    ) {
        get(Settings::getUrl().'/'.$group)
            ->assertOk();
    }
});
