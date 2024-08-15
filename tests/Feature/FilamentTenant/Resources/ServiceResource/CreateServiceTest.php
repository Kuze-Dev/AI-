<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceResource\Pages\CreateService;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\Service\Models\Service;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Database\Factories\MetaDataFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext()->features()->activate(ServiceBase::class);
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    CurrencyFactory::new()->createOne([
        'enabled' => true,
    ]);

});

it('can render page', function () {
    // Capture the Livewire payload for debugging
    livewire(CreateService::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create service', function () {

    Storage::fake(config('filament.default_filesystem_disk'));

    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new(['name' => 'category'])
        ->for(TaxonomyFactory::new()->withDummyBlueprint())
        ->createOne();

    $image = UploadedFile::fake()->image('preview.jpeg');

    $path = $image->store('/', config('filament.default_filesystem_disk'));

    $metaData = [
        'title' => 'Test Title',
        'keywords' => 'Test Keywords',
        'author' => 'Test Author',
        'description' => 'Test Description',
    ];

    $service = livewire(CreateService::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'retail_price' => 99.99,
            'selling_price' => 99.69,
            'billing_cycle' => 'monthly',
            'due_date_every' => 20,
            'taxonomy_term_id' => $taxonomyTerm->id,
            'media.0' => $image,
            'meta_data' => $metaData,
            'meta_data.image.0' => $path,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Service::class, [
        'name' => 'Test',
        'blueprint_id' => $blueprint->getKey(),
    ]);

    assertDatabaseHas(TaxonomyTerm::class, [
        'name' => 'category',
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $service->getMorphClass(),
                'model_id' => $service->getKey(),
            ]
        )
    );

    assertDatabaseHas(Media::class, [
        'mime_type' => $image->getMimeType(),
    ]);
});

it('cannot create service with same name', function () {

    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    ServiceFactory::new(['name' => 'Test'])
        ->withTaxonomyTerm()
        ->withDummyBlueprint()
        ->has(MetaDataFactory::new())
        ->isActive()
        ->createOne();

    $image = UploadedFile::fake()->image('preview.jpeg');

    livewire(CreateService::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'retail_price' => 99.99,
            'selling_price' => 99.69,
            'billing_cycle' => 'daily',
            'media.0' => $image,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertOk();
});

it('can create service with metadata', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new(['name' => 'category'])
        ->for(TaxonomyFactory::new()->withDummyBlueprint())
        ->createOne();

    $metaData = [
        'title' => 'Test Title',
        'keywords' => 'Test Keywords',
        'author' => 'Test Author',
        'description' => 'Test Description',
    ];

    $image = UploadedFile::fake()->image('preview.jpeg');

    $service = livewire(CreateService::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'retail_price' => 99.99,
            'selling_price' => 99.69,
            'billing_cycle' => 'monthly',
            'due_date_every' => 20,
            'meta_data' => $metaData,
            'taxonomy_term_id' => $taxonomyTerm->id,
            'media.0' => $image,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $service->getMorphClass(),
                'model_id' => $service->getKey(),
            ]
        )
    );
});

it('can create different types of service', function ($attribute) {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new(['name' => 'category'])
        ->for(TaxonomyFactory::new()->withDummyBlueprint())
        ->createOne();

    $image = UploadedFile::fake()->image('preview.jpeg');

    livewire(CreateService::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'retail_price' => 99.99,
            'selling_price' => 99.69,
            'is_subscription' => $attribute !== 'once',
            'billing_cycle' => $attribute !== 'once' ? $attribute : null,
            'due_date_every' => $attribute !== ('once' || 'daily') ? 20 : null,
            'taxonomy_term_id' => $taxonomyTerm->id,
            'media.0' => $image,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Service::class, [
        'is_subscription' => $attribute !== 'once',
        'billing_cycle' => $attribute !== 'once' ? $attribute : null,
    ]);
})->with(['daily', 'monthly', 'yearly', 'once']);
