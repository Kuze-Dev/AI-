<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\GlobalsResource\Pages\CreateGlobals;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Globals\Models\Globals;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render globals', function () {
    livewire(CreateGlobals::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create globals', function () {
    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField([
            'title' => 'Title',
            'type' => FieldType::TEXT,
        ])
        ->createOne();

    $globals = livewire(CreateGlobals::class)
        ->fillForm([
            'blueprint_id' => $blueprint->getKey(),
            'name' => 'Test',
            'slug' => 'test',
            'data' => ['main' => ['title' => 'Foo']],
        ])->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Globals::class, [
        'name' => $globals->name,
        'blueprint_id' => $blueprint->getKey(),
        'data' => json_encode(['main' => ['title' => 'Foo']]),
    ]);
});
