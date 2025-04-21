<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\SiteResource\Pages\ListSites;
use Domain\Site\Database\Factories\SiteFactory;
use Domain\Site\Models\Site;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(App\Features\CMS\SitesManagement::class);
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListSites::class)
        ->assertOk();
});

it('can list pages', function () {
    $pages = SiteFactory::new()
        ->count(5)
        ->create();

    livewire(ListSites::class)
        ->assertCanSeeTableRecords($pages)
        ->assertOk();
});

it('can delete site', function () {

    SiteFactory::new()
        ->count(2)
        ->create();

    livewire(ListSites::class)
        ->callTableAction('delete', 1);

    assertDatabaseCount(Site::class, 2);
});
