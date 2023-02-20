<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\GlobalsResource\Pages\EditGlobals;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Globals\Database\Factories\GlobalsFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render globals', function () {
    $record = GlobalsFactory::new()->withDummyBlueprint()->createOne();

    livewire(EditGlobals::class,  ['record' => $record->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful();
});

it('can edit globals', function () {
    $globals = GlobalsFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();

    livewire(EditGlobals::class, ['record' => $globals->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'slug' => 'test',
            'data.main.header' => 'Bar',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();
});

it('global blueprint must never be modified', function () {
    $globals = GlobalsFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )->create();

    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        ->createOne();

    livewire(EditGlobals::class, ['record' => $globals->first()->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'slug' => 'test',
            'blueprint_id' => $blueprint->id,
            'data.main.header' => 'Bar',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Globals::class, [
        'blueprint_id' => $globals->blueprint_id,
        'data' => json_encode(['main' => ['header' => 'Bar']]),
    ]);
});
