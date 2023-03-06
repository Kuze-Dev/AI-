<?php

declare(strict_types=1);

use App\Settings\SiteSettings;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

// TODO: Uncomment after https://github.com/spatie/laravel-route-attributes/pull/111 is merged
// it('can get settings', function ($settingsClass) {
//     /** @var \Spatie\LaravelSettings\Settings */
//     $settings = app($settingsClass);

//     getJson('api/settings/' . $settings::group())
//         ->assertOk()
//         ->assertJson(function (AssertableJson $json) use ($settings) {
//             $json
//                 ->where('data.id', $settings::group())
//                 ->where('data.type', 'settings')
//                 ->count('data.attributes', count($settings->toArray()))
//                 ->etc();
//         });
// })->with([SiteSettings::class]);
