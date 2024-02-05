<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\SiteResource\Pages\CreateSite;
use Domain\Site\Database\Factories\SiteFactory;
use Domain\Site\Models\Site;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(\App\Features\CMS\SitesManagement::class);
    loginAsSuperAdmin();
});

it('can render site', function () {
    livewire(CreateSite::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create site', function () {
    $site = livewire(CreateSite::class)
        ->fillForm([
            'name' => 'Test',
            'domain' => 'https://example.com',
        ])->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Site::class, [
        'name' => $site->name,
    ]);
});

it('can validate site name is unique', function () {
    $site = SiteFactory::new()
        ->createOne();

    livewire(CreateSite::class)
        ->fillForm([
            'name' => $site->name,
        ])->call('create')
        ->assertHasFormErrors();

    assertDatabaseHas(Site::class, [
        'name' => $site->name,
    ]);
});
