<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigrator;
use Tests\Fixtures\TestSettings;
use Tests\Fixtures\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses()->group('settings');

it('log settings', function () {
    $migrator = app(SettingsMigrator::class);
    $migrator->add('test_group.property1', 'old1 value');
    $migrator->add('test_group.property2', 'old2 value');

    actingAs($user = User::create([
        'email' => 'test@user',
        'active' => true,
    ]));

    app(config('activitylog.activity_model'))->truncate();

    $setting = app(TestSettings::class);
    $setting->property1 = 'new value';
    $setting->save();

    assertDatabaseCount(config('activitylog.activity_model'), 1);

    assertDatabaseHas(config('activitylog.activity_model'), [
        'causer_type' => $user->getMorphClass(),
        'causer_id' => $user->getKey(),
        'log_name' => 'test_group_settings',
        'description' => 'Test Group Settings Updated.',
        'properties' => json_encode([
            'old' => [
                'property1' => 'old1 value',
            ],
            'attributes' => [
                'property1' => 'new value',
            ],
        ]),
    ]);
});
