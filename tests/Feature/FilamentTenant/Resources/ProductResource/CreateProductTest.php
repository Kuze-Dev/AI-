<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ProductResource\Pages\CreateProduct;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Models\Product;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Http\UploadedFile;
use Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can render product', function () {
    livewire(CreateProduct::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create product', function () {
    $taxonomyTerm = TaxonomyTermFactory::new(['name' => 'Clothing'])
        ->for(TaxonomyFactory::new()->withDummyBlueprint())
        ->createOne();

    $productImage = UploadedFile::fake()->image('preview.jpeg');
    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Test',
            'sku' => 'TEST1234',
            'retail_price' => 24.99,
            'selling_price' => 29.99,
            'stock' => 50,
            'images.0' => $productImage,
            'weight' => 1,
            'length' => 10,
            'height' => 10,
            'width' => 3,
            'taxonomy_terms.0' => $taxonomyTerm->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Product::class, [
        'name' => 'Test',
        'sku' => 'TEST1234',
    ]);

    assertDatabaseHas(TaxonomyTerm::class, [
        'name' => 'Clothing',
    ]);

    assertDatabaseHas(Media::class, [
        'file_name' => $productImage->getClientOriginalName(),
        'mime_type' => $productImage->getMimeType(),
    ]);
});

it('can not create product with same name', function () {
    ProductFactory::new(['name' => 'Test'])->createOne();

    livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Test',
            'sku' => 'TEST1234',
            'retail_price' => 24.99,
            'selling_price' => 29.99,
            'stock' => 50,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertOk();
});

it('can create product with metadata', function () {
    $taxonomyTerm = TaxonomyTermFactory::new(['name' => 'Clothing'])
        ->for(TaxonomyFactory::new()->withDummyBlueprint())
        ->createOne();

    $metaData = [
        'title' => 'Test Title',
        'keywords' => 'Test Keywords',
        'author' => 'Test Author',
        'description' => 'Test Description',
    ];
    $imageFaker = UploadedFile::fake()->image('preview.jpeg');

    $product = livewire(CreateProduct::class)
        ->fillForm([
            'name' => 'Test',
            'sku' => 'TEST1234',
            'retail_price' => 24.99,
            'selling_price' => 29.99,
            'stock' => 50,
            'weight' => 15,
            'length' => 20,
            'width' => 15,
            'height' => 15,
            'images.0' => $imageFaker,
            'taxonomy_terms.0' => $taxonomyTerm->id,
            'meta_data' => $metaData,
            'meta_data.image.0' => $imageFaker,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Product::class, [
        'name' => 'Test',
        'sku' => 'TEST1234',
    ]);

    assertDatabaseHas(TaxonomyTerm::class, [
        'name' => 'Clothing',
    ]);

    assertDatabaseHas(Media::class, [
        'file_name' => $imageFaker->getClientOriginalName(),
        'mime_type' => $imageFaker->getMimeType(),
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $product->getMorphClass(),
                'model_id' => $product->getKey(),
            ]
        )
    );
});
