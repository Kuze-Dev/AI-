<?php

declare(strict_types=1);

use App\Filament\Clusters\Settings\Pages\SiteSettings as SiteSettingsComponent;
use App\Settings\SiteSettings;
use Illuminate\Http\UploadedFile;
use Spatie\Activitylog\ActivitylogServiceProvider;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

it('log settings', function () {
    $user = loginAsSuperAdmin();

    $setting = app(SiteSettings::class);
    $old = $setting->toCollection()
        ->except('front_end_domain')
        ->map(fn ($value) => blank($value) ? null : $value)->toArray();

    livewire(SiteSettingsComponent::class)
        ->fillForm([
            'name' => 'new name',
            'description' => 'new description',
            'author' => 'new author',
            'logo' => UploadedFile::fake()->image('test.png'),
            'favicon' => UploadedFile::fake()->image('test.png'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(ActivitylogServiceProvider::determineActivityModel(), [
        'causer_type' => $user->getMorphClass(),
        'causer_id' => $user->getKey(),
        'log_name' => 'admin',
        'description' => 'Site Settings updated',
        'properties' => json_encode([
            'attributes' => app(SiteSettings::class)
                ->toCollection()
                ->except('front_end_domain')
                ->toArray(),
            'old' => $old,
        ]),
    ]);
})->todo();
