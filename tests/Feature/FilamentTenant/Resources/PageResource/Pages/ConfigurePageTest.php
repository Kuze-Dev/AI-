<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\ConfigurePage;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Models\Page;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsAdmin();
});

it('can render page', function () {
    $page = PageFactory::new()->withDummyBlueprint()->createOne();

    livewire(ConfigurePage::class, ['record' => $page->getRouteKey()])
        ->assertFormExists()
        ->assertOk();
});

it('can configure page', function () {
    $page = PageFactory::new()->withDummyBlueprint()->createOne();

    livewire(ConfigurePage::class, ['record' => $page->getRouteKey()])
        ->fillForm(['name' => 'Test'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});
