<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\SliceResource\Slices\CreateSlice;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
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
    livewire(CreateSlice::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create slice', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    livewire(CreateSlice::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Slice::class, [
        'name' => 'Test',
        'component' => 'Test',
        'blueprint_id' => $blueprint->id,
    ]);
});

it('can not create slice with same name', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    SliceFactory::new(['name' => 'Test'])
        ->withDummyBlueprint()
        ->createOne();

    livewire(CreateSlice::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertOk();
});

it('can not create slice with same component', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    SliceFactory::new(['component' => 'Test'])
        ->withDummyBlueprint()
        ->createOne();

    livewire(CreateSlice::class)
        ->fillForm([
            'name' => 'Test',
            'component' => 'Test',
            'blueprint_id' => $blueprint->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['component' => 'unique'])
        ->assertOk();
});
