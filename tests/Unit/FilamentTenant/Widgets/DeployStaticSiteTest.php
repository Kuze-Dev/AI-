<?php

declare(strict_types=1);

use App\FilamentTenant\Widgets\DeployStaticSite;
use App\Settings\CMSSettings;
use Filament\Facades\Filament;
use Spatie\Activitylog\ActivitylogServiceProvider;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('log', function () {

    \Illuminate\Support\Facades\Http::fake([
        'https://sample-deploy-hook/test' => Http::response('ok'),
    ]);
    CMSSettings::fake([
        'deploy_hook' => 'https://sample-deploy-hook/test',
    ]);

    $activityModel = ActivitylogServiceProvider::determineActivityModel();
    $activityModel::truncate();

    livewire(DeployStaticSite::class)
        ->call('deploy');

    assertDatabaseCount($activityModel, 1);
    $loggedInAdmin = Filament::auth()->user();
    assertDatabaseHas(
        $activityModel,
        [
            'causer_type' => $loggedInAdmin->getMorphClass(),
            'causer_id' => $loggedInAdmin->getKey(),
            'subject_type' => null,
            'subject_id' => null,
            'log_name' => 'admin',
            'event' => 'deployed-hook',
            'properties' => json_encode([
                'custom' => [
                    'deploy_hook' => 'https://sample-deploy-hook/test',
                ],
            ]),
            'description' => 'Deployed hook',
        ]
    );
});
