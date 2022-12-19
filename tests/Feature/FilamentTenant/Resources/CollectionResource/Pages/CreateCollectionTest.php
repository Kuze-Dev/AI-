<?php 

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\CreateCollection;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Collection\Models\Collection;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render collection', function () {
    livewire(CreateCollection::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create collection', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    livewire(CreateCollection::class)
        ->fillForm([
            'name' => 'Test Collection',
            'blueprint_id' => $blueprint->getKey(),
            'future_publish_date' => 'public',
            'past_publish_date' => 'unlisted'
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    
    assertDatabaseCount(Collection::class, 1);
});