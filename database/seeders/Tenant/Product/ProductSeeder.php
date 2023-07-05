<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Product;

use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        ProductOptionValue::truncate();
        ProductOption::truncate();
        ProductVariant::truncate();
        DB::table('product_taxonomy_term')->truncate();
        Product::truncate();

        (new ProductFactory())->seedData();
    }

    public function data(): array
    {
        return [
            'products' => [
                [
                    'name' => 'T-Shirt',
                    'sku' => 'TSHR1924',
                    'description' => 'A comfortable cotton t-shirt.',
                    'selling_price' => 19.99,
                    'retail_price' => 24.99,
                    'stock' => 212,
                    'image_url' => 'https://images.pexels.com/photos/996329/pexels-photo-996329.jpeg?auto=compress&cs=tinysrgb&w=1600',
                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 0.50,

                ],
                [
                    'name' => 'Jeans',
                    'sku' => 'JENS4954',
                    'description' => 'Classic denim jeans for everyday wear.',
                    'selling_price' => 49.99,
                    'retail_price' => 54.99,
                    'stock' => 90,
                    'image_url' => 'https://images.pexels.com/photos/603022/pexels-photo-603022.jpeg?auto=compress&cs=tinysrgb&w=1600',
                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 2.25,
                ],
                [
                    'name' => 'Dress',
                    'sku' => 'DRSS7984',
                    'description' => 'An elegant dress for special occasions.',
                    'selling_price' => 79.99,
                    'retail_price' => 84.99,
                    'stock' => 75,
                    'image_url' => 'https://images.pexels.com/photos/7089430/pexels-photo-7089430.jpeg?auto=compress&cs=tinysrgb&w=1600',
                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 1.25,
                ],
                [
                    'name' => 'Blouse',
                    'sku' => 'BLOU2327',
                    'description' => 'Comfortable loose overgarment that resembles a shirt or smock.',
                    'selling_price' => 23.99,
                    'retail_price' => 27.99,
                    'stock' => 155,
                    'image_url' => 'https://images.pexels.com/photos/1036623/pexels-photo-1036623.jpeg?auto=compress&cs=tinysrgb&w=1600',
                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 0.50,
                ],
                [
                    'name' => 'Hoodies',
                    'sku' => 'HOOD4449',
                    'description' => 'A cozy hoodie for a casual and warm style.',
                    'selling_price' => 44.99,
                    'retail_price' => 49.99,
                    'stock' => 88,
                    'image_url' => 'https://images.pexels.com/photos/634785/pexels-photo-634785.jpeg?auto=compress&cs=tinysrgb&w=1600',
                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 2.75,
                ],
                [
                    'name' => 'Tank Top',
                    'sku' => 'TKTP1419',
                    'description' => 'A stylish tank top for hot summer days.',
                    'selling_price' => 14.99,
                    'retail_price' => 19.99,
                    'stock' => 111,
                    'image_url' => 'https://images.pexels.com/photos/2775417/pexels-photo-2775417.jpeg?auto=compress&cs=tinysrgb&w=1600',
                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 0.80,
                ],
                [
                    'name' => 'Polo Shirt',
                    'sku' => 'POLS3439',
                    'description' => 'A stylish polo shirt for a smart-casual look.',
                    'selling_price' => 34.99,
                    'retail_price' => 39.99,
                    'stock' => 177,
                    'image_url' => 'https://images.pexels.com/photos/3228934/pexels-photo-3228934.jpeg?auto=compress&cs=tinysrgb&w=1600',
                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 0.90,
                ],
                [
                    'name' => 'Sweatshirt',
                    'sku' => 'SWSH3944',
                    'description' => 'A cozy sweatshirt for chilly days.',
                    'selling_price' => 39.99,
                    'retail_price' => 44.99,
                    'stock' => 133,
                    'image_url' => 'https://images.pexels.com/photos/845434/pexels-photo-845434.jpeg?auto=compress&cs=tinysrgb&w=1600',
                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 2.60,
                ],
                [
                    'name' => 'Cargo pants',
                    'sku' => 'CAGO5664',
                    'description' => 'Comfy, loosely cut pants originally designed for rough work 
                        environments and outdoor activities.',
                    'selling_price' => 59.99,
                    'retail_price' => 64.99,
                    'stock' => 124,
                    'image_url' => 'https://images.pexels.com/photos/17037280/pexels-photo-17037280/free-photo-of-model-posing-in-black-jacket-and-cargo-pants.jpeg?auto=compress&cs=tinysrgb&w=1600',

                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 3.80,
                ],
                [
                    'name' => 'Swimming trunks',
                    'sku' => 'SWTR4348',
                    'description' => 'light-weight shorts that stop at varying distances above the knee',
                    'selling_price' => 43.99,
                    'retail_price' => 48.99,
                    'stock' => 188,
                    'image_url' => 'https://images.pexels.com/photos/9963946/pexels-photo-9963946.jpeg?auto=compress&cs=tinysrgb&w=1600',
                    'dimension' => [
                        'length' => 23,
                        'width' => 23,
                        'height' => 23,
                    ],
                    'weight' => 1.40,
                ],
            ],
            'product_options' => [
                [
                    'name' => 'size',
                    'values' => [
                        'large',
                    ],
                ],
                [
                    'name' => 'color',
                    'values' => [
                        'white',
                        'black',

                    ],
                ],
            ],
            'variant_combinations' => [
                [
                    'size' => 'large',
                    'color' => 'white',
                ],
                [
                    'size' => 'large',
                    'color' => 'black',
                ],
            ],
            'taxonomies' => [
                [
                    'name' => 'Brand',
                    'term' => [
                        ['name' => 'Brand One'],
                        ['name' => 'The Next Brand'],
                    ],
                ],
                [
                    'name' => 'Categories',
                    'term' => [
                        ['name' => 'Clothing'],
                        ['name' => 'Food & Beverages'],
                        ['name' => 'Electronics'],
                    ],
                ],
            ],
            'blueprint_for_taxonomy' => [
                "name" => "Image with Heading Block Blueprint",
                "schema" => [
                    'sections' => [
                        [

                            "title" => "Main",
                            "fields" => [
                                [
                                    "max" => null,
                                    "min" => null,
                                    "step" => null,
                                    "type" => "text",
                                    "rules" => [
                                        "required",
                                        "string"
                                    ],
                                    "title" => "Heading",
                                    "max_length" => null,
                                    "min_length" => null,
                                    "state_name" => "heading"
                                ],
                                [
                                    "type" => "file",
                                    "rules" => [
                                        "required",
                                        "image"
                                    ],
                                    "title" => "Image",
                                    "accept" => [],
                                    "reorder" => false,
                                    "max_size" => null,
                                    "min_size" => null,
                                    "multiple" => false,
                                    "max_files" => null,
                                    "min_files" => null,
                                    "state_name" => "image"
                                ]
                            ],
                            "state_name" => "main"
                        ],
                    ],
                ],
            ]
        ];
    }
}
