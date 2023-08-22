<?php

declare(strict_types=1);

use Domain\Site\Models\Site;
use Filament\Facades\Filament;
use Domain\Site\Database\Factories\SiteFactory;

use App\FilamentTenant\Resources\SiteResource\Pages\ListSites;

use function Pest\Livewire\livewire;
use function Pest\Laravel\assertDatabaseCount;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    tenancy()->tenant?->features()->activate(\App\Features\CMS\SitesManagement::class);
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
