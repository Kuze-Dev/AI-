<?php

declare(strict_types=1);

use Domain\Product\Actions\CreateProductAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can create product', function () {
    $taxonomyTerm = TaxonomyTermFactory::new(['name' => 'Clothing'])
        ->for(TaxonomyFactory::new()->withDummyBlueprint())
        ->createOne();

    $productImage = UploadedFile::fake()->image('preview.jpeg');

    $product = app(CreateProductAction::class)
        ->execute(ProductData::fromArray([
            'name' => 'Test',
            'sku' => 'TEST1234',
            'description' => 'Test Description',
            'retail_price' => 24.99,
            'selling_price' => 29.99,
            'stock' => 50,
            'taxonomy_terms' => [
                $taxonomyTerm->id,
            ],
            'images' => [$productImage],
            'meta_data' => [
                'title' => 'Test',
                'author' => '',
                'keywords' => '',
                'description' => '',
            ],
            'length' => 20,
            'width' => 15,
            'height' => 15,
            'weight' => 15,
            'status' => true,
            'minimum_order_quantity' => 1,
            'is_featured' => true,
            'is_special_offer' => true,
            'allow_customer_remarks' => true,
            'product_options' => [],
            'product_variants' => [],
        ]));

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

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'Test',
            'author' => '',
            'keywords' => '',
            'description' => '',
            'model_type' => $product->getMorphClass(),
            'model_id' => $product->getKey(),
        ]
    );
});
