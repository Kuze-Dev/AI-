<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\EditCollection;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Collection\Models\Collection;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render collection', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->for($taxonomy)
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    livewire(EditCollection::class, ['record' => $collection->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ])
        ->assertOk();
});

it('can edit collection', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->for($taxonomy)
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $newData = [
        'name' => 'Test Collection Updated',
        'future_publish_date_behavior' => 'public',
        'past_publish_date_behavior' => 'private',
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
