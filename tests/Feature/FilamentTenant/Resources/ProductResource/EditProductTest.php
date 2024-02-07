<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ProductResource\Pages\EditProduct;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductOptionFactory;
use Domain\Product\Database\Factories\ProductOptionValueFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;
use Domain\Product\Models\Product;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render product', function () {
    $product = ProductFactory::new()
        ->has(TaxonomyTermFactory::new()->for(TaxonomyFactory::new()->withDummyBlueprint())->count(2))
        ->has(ProductOptionFactory::new()->has(ProductOptionValueFactory::new()))
        ->has(ProductVariantFactory::new())
        ->has(MetaDataFactory::new())
        ->createOne();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $product->name,
            'images.0' => $product->getMedia()->first(),
            'dimension' => $product->dimension,
            'sku' => $product->sku,
            'retail_price' => $product->retail_price,
            'selling_price' => $product->selling_price,
            'stock' => $product->stock,
        ])
        ->assertOk();
});

it('can edit product', function () {
    $product = ProductFactory::new()
        ->has(TaxonomyTermFactory::new()->for(TaxonomyFactory::new()->withDummyBlueprint())->count(2))
        ->has(ProductOptionFactory::new()->has(ProductOptionValueFactory::new()))
        ->has(ProductVariantFactory::new()->setCombination())
        ->has(MetaDataFactory::new())
        ->createOne();

    $metaData = [
        'title' => 'Foo title updated',
        'description' => 'Foo description updated',
        'author' => 'Foo author updated',
        'keywords' => 'Foo keywords updated',
    ];
    $dataImage = UploadedFile::fake()->image('preview.jpeg');

    $updatedProduct = livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm([
            'name' => 'Test Title Updated',
            'description' => 'Test Description Updated',
            'images.0' => $dataImage,
            'status' => ! $product->status,
            'meta_data' => $metaData,
            'meta_data.image.0' => $dataImage,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Product::class, [
        'name' => 'Test Title Updated',
        'description' => 'Test Description Updated',
        'status' => $updatedProduct->status,
        'updated_at' => $updatedProduct->updated_at,
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

    assertDatabaseHas(Media::class, [
        'file_name' => $dataImage->getClientOriginalName(),
        'mime_type' => $dataImage->getMimeType(),
    ]);
});
