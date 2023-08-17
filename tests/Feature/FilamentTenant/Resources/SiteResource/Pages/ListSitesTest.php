<?php

declare(strict_types=1);

use Domain\Site\Models\Site;
use Filament\Facades\Filament;
use Domain\Site\Database\Factories\SiteFactory;

use App\FilamentTenant\Resources\SiteResource\Pages\ListSites;

use function Pest\Livewire\livewire;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertSoftDeleted;

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

it('can edit site', function () {
    livewire(ListSites::class)
        ->callPageAction('create', ['name' => 'Site 1'])
        ->callTableAction('edit', 1, ['name' => 'Site 2']);

    assertDatabaseHas(Site::class, ['name' => 'Site 2']);
    assertDatabaseCount(Site::class, 1);
});

it('can delete site', function () {
    livewire(ListSites::class)
        ->callPageAction('create', ['name' => 'Site 1'])
        ->callPageAction('create', ['name' => 'Site 2'])
        ->callTableAction('delete', 1);

    assertSoftDeleted(Site::class, ['name' => 'Site 1']);
    assertDatabaseCount(Site::class, 2);
});
