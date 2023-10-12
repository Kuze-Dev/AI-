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
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    tenancy()->tenant->features()->activate(ServiceBase::class);
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
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new(['name' => 'category'])
        ->for(TaxonomyFactory::new()->withDummyBlueprint())
        ->createOne();

    $image = UploadedFile::fake()->image('preview.jpeg');

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
            'billing_cycle' => 'Daily',
            'due_date_every' => 20,
            'is_featured' => false,
            'is_special_offer' => false,
            'pay_upfront' => false,
            'is_subscription' => false,
            'status' => false,
            'taxonomy_term_id' => $taxonomyTerm->id,
            'images.0' => $image,
            'meta_data' => $metaData,
            'meta_data.image.0' => $image,
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
        'file_name' => $image->getClientOriginalName(),
        'mime_type' => $image->getMimeType(),
    ]);
});

it('cannot create service with same name', function () {

    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    ServiceFactory::new()->createOne([
        'name' => 'Test',
        'blueprint_id' => $blueprint->getKey(),
        'retail_price' => 99.99,
        'selling_price' => 99.69,
        'billing_cycle' => 'Daily',
        'due_date_every' => 20,
        'is_featured' => false,
        'is_special_offer' => false,
        'pay_upfront' => false,
        'is_subscription' => false,
        'status' => false,
    ]);

    livewire(CreateService::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'retail_price' => 99.99,
            'selling_price' => 99.69,
            'billing_cycle' => 'Daily',
            'due_date_every' => 20,
            'is_featured' => false,
            'is_special_offer' => false,
            'pay_upfront' => false,
            'is_subscription' => false,
            'status' => false,
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

    $service = livewire(CreateService::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'retail_price' => 99.99,
            'selling_price' => 99.69,
            'billing_cycle' => 'Daily',
            'due_date_every' => 20,
            'is_featured' => false,
            'is_special_offer' => false,
            'pay_upfront' => false,
            'is_subscription' => false,
            'status' => false,
            'meta_data' => $metaData,
            'taxonomy_term_id' => $taxonomyTerm->id,
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
