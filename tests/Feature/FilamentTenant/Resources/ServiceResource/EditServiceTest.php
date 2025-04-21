<?php

declare(strict_types=1);

namespace Tests\Feature\FilamentTenant\Resources\ServiceResource;

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceResource\Pages\EditService;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\Service\Models\Service;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(ServiceBase::class);
    loginAsSuperAdmin();

    CurrencyFactory::new()->createOne([
        'enabled' => true,
    ]);
});

it('can render service', function () {
    $service = ServiceFactory::new()
        ->withTaxonomyTerm()
        ->withDummyBlueprint()
        ->has(MetaDataFactory::new())
        ->isActive()
        ->withBillingCycle()
        ->createOne();

    livewire(EditService::class, ['record' => $service->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $service->name,
            'description' => $service->description,
            'blueprint_id' => $service->blueprint_id,
            'retail_price' => $service->retail_price,
            'selling_price' => $service->selling_price,
            'billing_cycle' => $service->billing_cycle?->value,
            'due_date_every' => $service->due_date_every,
            'is_featured' => $service->is_featured,
            'is_special_offer' => $service->is_special_offer,
            'pay_upfront' => $service->pay_upfront,
            'is_subscription' => $service->is_subscription,
            'status' => $service->status,
            'needs_approval' => $service->needs_approval,
            'is_auto_generated_bill' => $service->is_auto_generated_bill,
            'is_partial_payment' => $service->is_partial_payment,
            //            'is_installment' => $service->is_installment,
        ])
        ->assertOk();
});

it('can edit service', function () {

    Storage::fake(config('filament.default_filesystem_disk'));

    $service = ServiceFactory::new()
        ->withDummyBlueprint()
        ->has(TaxonomyTermFactory::new()->for(TaxonomyFactory::new()->withDummyBlueprint()))
        ->has(MetaDataFactory::new())
        ->isActive()
        ->withBillingCycle()
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

    livewire(EditService::class, ['record' => $service->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'retail_price' => 99.99,
            'selling_price' => 99.69,
            'billing_cycle' => 'daily',
            'due_date_every' => 20,
            'is_featured' => ! $service->is_featured,
            'is_special_offer' => ! $service->is_special_offer,
            'pay_upfront' => ! $service->pay_upfront,
            'is_subscription' => ! $service->is_subscription,
            'status' => ! $service->status,
            'needs_approval' => ! $service->needs_approval,
            'is_auto_generated_bill' => ! $service->is_auto_generated_bill,
            'is_partial_payment' => ! $service->is_partial_payment,
            //            'is_installment' => ! $service->is_installment,
            'taxonomyTerms' => [$taxonomyTerm->id],
            'media.0' => $image,
            'metaData' => $metaData,
            'metaData.image.0' => $path,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Service::class, [
        'name' => 'Test',
    ]);

    assertDatabaseHas(TaxonomyTerm::class, [
        'name' => 'category',
    ]);

    assertDatabaseHas(
        MetaData::class,
        [
            'model_type' => $service->getMorphClass(),
            'model_id' => $service->getKey(),
            ...$metaData,
        ]
    );

    assertDatabaseHas(Media::class, [
        'mime_type' => $image->getMimeType(),
    ]);
});
