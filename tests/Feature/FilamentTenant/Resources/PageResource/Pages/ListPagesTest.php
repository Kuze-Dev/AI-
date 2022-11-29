<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\ListPages;
use Domain\Page\Database\Factories\PageFactory;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListPages::class)
        ->assertOk();
});

it('can list pages', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->assertOk();
});

it('can filter pages by blueprint', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    $blueprint = $pages->random()->blueprint;

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->filterTable('blueprint', $blueprint->id)
        ->assertCanSeeTableRecords($pages->where('blueprint_id', $blueprint->id))
        ->assertCanNotSeeTableRecords($pages->where('blueprint_id', '!=', $blueprint->id))
        ->assertOk();
});

it('can delete page', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(ListPages::class)
        ->callTableAction(DeleteAction::class, $pages)
        ->assertOk();

    assertModelMissing($pages);
});
