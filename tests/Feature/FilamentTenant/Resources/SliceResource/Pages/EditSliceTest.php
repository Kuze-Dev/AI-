<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\SliceResource\Slices\EditSlice;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Page\Models\Slice;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    $slice = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(EditSlice::class, ['record' => $slice->getRouteKey()])
        ->assertSuccessful()
        ->assertFormExists()
        ->assertFormSet([
            'name' => $slice->name,
            'component' => $slice->component,
            'blueprint_id' => $slice->blueprint_id,
        ])
        ->assertOk();
});

it('can edit page', function () {
    $slice = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(EditSlice::class, ['record' => $slice->getRouteKey()])
        ->fillForm([
            'name' => 'Foo',
            'component' => 'Foo',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Slice::class, [
        'name' => 'Foo',
        'component' => 'Foo',
    ]);
});
