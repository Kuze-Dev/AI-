<?php 

declare (strict_types = 1);

use App\FilamentTenant\Resources\CollectionResource\Pages\EditCollection;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Collection\Models\Collection;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it ('can render collection', function () {
    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date' => 'public',
            'past_publish_date' => 'unlisted'
        ]);

    livewire(EditCollection::class, [ 'record' => $collection->getRouteKey() ])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => 'Test Collection',
            'future_publish_date' => 'public',
            'past_publish_date' => 'unlisted'
        ])
        ->assertOk();
});

it ('can edit collection', function () {
    $collection = CollectionFactory::new()
    ->for(
        BlueprintFactory::new()
            ->addSchemaSection(['title' => 'Main'])
            ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
    )
    ->createOne([
        'name' => 'Test Collection',
        'future_publish_date' => 'private',
        'past_publish_date' => 'unlisted',
    ]);

    $newData = [        
        'name' => 'Test Collection Updated',
        'future_publish_date' => 'public',
        'past_publish_date' => 'private',
    ];
    

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(
        Collection::class,
        $newData
    );
});