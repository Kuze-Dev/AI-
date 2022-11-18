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
    loginAsAdmin();
});

it('can render page', function () {
    livewire(ListPages::class)
        ->assertOk();
});

it('can list pages', function () {
    $blueprints = PageFactory::new()->count(5)->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($blueprints)
        ->assertOk();
});

it('can delete page', function () {
    $blueprint = PageFactory::new()->createOne();

    livewire(ListPages::class)
        ->callTableAction(DeleteAction::class, $blueprint)
        ->assertOk();

    assertModelMissing($blueprint);
});
