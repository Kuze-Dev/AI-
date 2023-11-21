<?php

declare(strict_types=1);

use Domain\Product\Actions\UpdateProductAction;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductOptionFactory;
use Domain\Product\Database\Factories\ProductOptionValueFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Illuminate\Http\UploadedFile;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\MetaData\Models\MetaData;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update product', function () {
    $taxonomyTerm = TaxonomyTermFactory::new(['name' => 'Clothing'])
        ->for(TaxonomyFactory::new()->withDummyBlueprint())
        ->createOne();
    $product = ProductFactory::new()
        ->has(ProductOptionFactory::new()->has(ProductOptionValueFactory::new()))
        ->has(ProductVariantFactory::new())
        ->createOne();

    $product->taxonomyTerms()->sync($taxonomyTerm->id);

    $productOption = $product->productOptions()->first();
    $optionValue = $productOption->productOptionValues->first();
    $productVariant = $product->productVariants->first();

    $productImage = UploadedFile::fake()->image('preview.jpeg');

    $metaData = [
        'title' => $product->slug,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ];

    MetaDataFactory::new($metaData)->for($product);

    app(UpdateProductAction::class)
        ->execute(
            $product,
            ProductData::fromArray([
                'name' => 'Foo',
                'sku' => 'FOO01234',
                'description' => 'Foo Description',
                'retail_price' => 24.99,
                'selling_price' => 29.99,
                'stock' => 50,
                'taxonomy_terms' => [
                    $taxonomyTerm->id,
                ],
                'images' => [$productImage],
                'videos' => [],
                'meta_data' => [
                    'title' => 'foo title updated',
                    'author' => 'foo author updated',
                    'keywords' => 'foo keywords updated',
                    'description' => 'foo description updated',
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
                'allow_stocks' => true,
                'allow_guest_purchase' => true,
                'product_options' => [
                    [
                        [
                            'id' => $productOption->id,
                            'name' => 'size',
                            'slug' => 'size',
                            'is_custom' => false,
                            'productOptionValues' => [
                                [
                                    'id' => $optionValue->id,
                                    'name' => 'small',
                                    'slug' => 'small',
                                    'product_option_id' => $productOption->id,
                                    'icon_type' => 'text',
                                    'icon_value' => null,
                                    'images' => [],
                                ],
                            ],
                        ],
                    ],
                ],
                'product_variants' => [
                    [
                        'combination' => [
                            [
                                'option' => 'size',
                                'option_id' => $productOption->id,
                                'option_value' => 'small',
                                'option_value_id' => $optionValue->id,
                            ],
                        ],
                        'id' => $productVariant->id,
                        'selling_price' => '58.45',
                        'retail_price' => '90.95',
                        'stock' => 11,
                        'status' => true,
                        'sku' => 'SKU4300',
                    ],
                ],
            ])
        );

    assertDatabaseHas(Product::class, ['name' => 'Foo']);

    assertDatabaseHas(ProductOption::class, [
        'product_id' => $product->id,
        'name' => 'size',
    ]);

    assertDatabaseHas(ProductOptionValue::class, [
        'product_option_id' => $productOption->id,
        'name' => 'small',
    ]);

    assertDatabaseHas(ProductVariant::class, [
        'product_id' => $product->id,
        'sku' => 'SKU4300',
        'combination' => json_encode(
            [
                [
                    'option' => 'size',
                    'option_id' => $productOption->id,
                    'option_value' => 'small',
                    'option_value_id' => $optionValue->id,
                ],
            ]
        ),
    ]);

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'foo title updated',
            'author' => 'foo author updated',
            'keywords' => 'foo keywords updated',
            'description' => 'foo description updated',
            'model_type' => $product->getMorphClass(),
            'model_id' => $product->getKey(),
        ]
    );
});
