<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\SiteResource\Pages\EditSite;
use Domain\Site\Database\Factories\SiteFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(\App\Features\CMS\SitesManagement::class);
    loginAsSuperAdmin();
});

it('can render site', function () {
    $record = SiteFactory::new()->createOne();

    livewire(EditSite::class, ['record' => $record->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful();
});

it('can edit site', function () {
    $site = SiteFactory::new()
        ->createOne();

    livewire(EditSite::class, ['record' => $site->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'domain' => 'https://example.com',
            'slug' => 'test',
            'site_manager' => [],
            'data.main.header' => 'Bar',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();
});
