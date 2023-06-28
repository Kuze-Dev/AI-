<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Product;

use Domain\Product\Database\Factories\ProductFactory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
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
                    'shipping_fee' => 5,
                    'stock' => 212,
                ],
                [
                    'name' => 'Jeans',
                    'sku' => 'JENS4954',
                    'description' => 'Classic denim jeans for everyday wear.',
                    'selling_price' => 49.99,
                    'retail_price' => 54.99,
                    'shipping_fee' => 5,
                    'stock' => 90,
                ],
                [
                    'name' => 'Dress',
                    'sku' => 'DRSS7984',
                    'description' => 'An elegant dress for special occasions.',
                    'selling_price' => 79.99,
                    'retail_price' => 84.99,
                    'shipping_fee' => 5,
                    'stock' => 75,
                ],
                [
                    'name' => 'Blouse',
                    'sku' => 'BLOU2327',
                    'description' => 'Comfortable loose overgarment that resembles a shirt or smock.',
                    'selling_price' => 23.99,
                    'retail_price' => 27.99,
                    'shipping_fee' => 5,
                    'stock' => 155,
                ],
                [
                    'name' => 'Hoodies',
                    'sku' => 'HOOD4449',
                    'description' => 'A cozy hoodie for a casual and warm style.',
                    'selling_price' => 44.99,
                    'retail_price' => 49.99,
                    'shipping_fee' => 5,
                    'stock' => 88,
                ],
                [
                    'name' => 'Tank Top',
                    'sku' => 'TKTP1419',
                    'description' => 'A stylish tank top for hot summer days.',
                    'selling_price' => 14.99,
                    'retail_price' => 19.99,
                    'shipping_fee' => 5,
                    'stock' => 111,
                ],
                [
                    'name' => 'Polo Shirt',
                    'sku' => 'POLS3439',
                    'description' => 'A stylish polo shirt for a smart-casual look.',
                    'selling_price' => 34.99,
                    'retail_price' => 39.99,
                    'shipping_fee' => 5,
                    'stock' => 177,
                ],
                [
                    'name' => 'Sweatshirt',
                    'sku' => 'SWSH3944',
                    'description' => 'A cozy sweatshirt for chilly days.',
                    'selling_price' => 39.99,
                    'retail_price' => 44.99,
                    'shipping_fee' => 5,
                    'stock' => 133,
                ],
                [
                    'name' => 'Cargo pants',
                    'sku' => 'CAGO5664',
                    'description' => 'Comfy, loosely cut pants originally designed for rough work 
                        environments and outdoor activities.',
                    'selling_price' => 59.99,
                    'retail_price' => 64.99,
                    'shipping_fee' => 5,
                    'stock' => 124,
                ],
                [
                    'name' => 'Swimming trunks',
                    'sku' => 'SWTR4348',
                    'description' => 'light-weight shorts that stop at varying distances above the knee',
                    'selling_price' => 43.99,
                    'retail_price' => 48.99,
                    'shipping_fee' => 5,
                    'stock' => 188,
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
        ];
    }
}
