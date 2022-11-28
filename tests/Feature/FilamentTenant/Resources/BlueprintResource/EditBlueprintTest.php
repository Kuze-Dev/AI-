<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlueprintResource\Pages\EditBlueprint;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\Blueprint;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField([
            'title' => 'Title',
            'type' => FieldType::TEXT,
        ])
        ->createOne();

    livewire(EditBlueprint::class, ['record' => $blueprint->getKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $blueprint->name,
        ]);
});

it('can edit blueprint', function () {
    $blueprint = BlueprintFactory::new()->withDummySchema()->createOne();

    livewire(EditBlueprint::class, ['record' => $blueprint->getKey()])
        ->fillForm([
            'name' => 'Test',
            'schema' => [
                'sections' => [
                    [
                        'title' => 'Main',
                        'fields' => [
                            [
                                'title' => 'Foo',
                                'type' => 'text',
                            ],
                        ],
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Blueprint::class, ['name' => 'Test']);
});
