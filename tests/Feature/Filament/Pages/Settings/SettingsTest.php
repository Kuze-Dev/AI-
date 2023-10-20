<?php

declare(strict_types=1);

use App\Filament\Pages\Settings\Settings;
use App\Settings\SiteSettings;

use function Pest\Laravel\get;

beforeEach(fn () => loginAsSuperAdmin());

it('can render page', function () {
    get(Settings::getUrl())
        ->assertOk();
});

it('can render page by groups', function (string $settingClass) {
    get(Settings::getUrl().'/'.$settingClass::group())
        ->assertOk();
})->with([SiteSettings::class]);
