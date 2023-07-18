<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\FilamentTenant\Resources\ProductResource;
use Domain\Product\Actions\CreateProductAction;
use Domain\Product\Actions\UpdateProductAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Taxation\Models\TaxZone;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Support\Excel\Actions\ExportAction;
use Support\Excel\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Support\MetaData\DataTransferObjects\MetaDataData;
use Illuminate\Validation\Rule;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make()
                ->model(Product::class)
                ->processRowsUsing(
                    function (array $row): Product {
                        $data = [
                            'images' => $row['image_link'],
                            'name' => $row['name'],
                            'categories' => $row['category'],
                            'brand' => $row['brand'],
                            'sku' => $row['sku'],
                            'stock' => $row['stock'],
                            'retail_price' => $row['retail_price'],
                            'selling_price' => $row['selling_price'],
                            // 'status' => $row['status'],
                            // 'is_digital_product' => $row['is_digital_product'],
                            // 'is_featured' => $row['is_featured'],
                            // 'is_special_offer' => $row['is_special_offer'],
                            // 'allow_customer_remarks' => $row['allow_customer_remarks'],
                            'weight' => $row['weight'],
                            'length' => $row['length'],
                            'width' => $row['width'],
                            'height' => $row['height'],
                            // 'minimum_order_quantity' => $row['minimum_order_quantity'],
                        ];

                        // Taxonomies (Category & Product)
                        $taxoTermIds = [];
                        foreach (['categories' => $data['categories'], 'brand' => $data['brand']] as $key => $taxonomyTerm) {
                            $taxonomy = Taxonomy::with('taxonomyTerms')
                                ->whereSlug($key)
                                ->whereHas('taxonomyTerms', function (Builder $query) use ($taxonomyTerm) {
                                    $query->where('name', $taxonomyTerm);
                                })->first();

                            if (!$taxonomy) {
                                $foundTaxo = Taxonomy::where('slug', $key)->first();
                                // dd($key, $foundTaxo);
                                if ($foundTaxo) {
                                    $model = TaxonomyTerm::create(['taxonomy_id' => $foundTaxo->id, 'data' => [
                                        'main' => [
                                            'heading' => $taxonomyTerm,
                                        ],
                                    ], 'name' => $taxonomyTerm]);

                                    array_push($taxoTermIds, $model->id);
                                }
                            }
                        }
                        $data['taxonomy_terms'] = $taxoTermIds;

                        unset($row);
                        unset($data['categories']);
                        unset($data['brand']);
                        $data['meta_data'] = new MetaDataData($data['name']);
                        $product = app(CreateProductAction::class)->execute(new ProductData(...$data));

                        return $product;
                    }
                )
                ->withValidation(
                    rules: [
                        'name' => 'required|string|min:3|max:100',
                    ],
                ),
            ExportAction::make()
                ->model(Product::class)
                ->queue()
                ->query(fn (Builder $query) => $query->latest())
                ->mapUsing(
                    [
                        'Name', 'SKU', 'Description', 'Retail Price', 'Selling Price', 'Stock',
                        'Status', 'Is Digital Product', 'Is Featured', 'Is Special Offer',
                        'Allow Customer Remarks', 'Weight', 'Dimension', 'Minimum Order Quantity',
                        'Created At'
                    ],
                    fn (Product $product): array => [
                        $product->name,
                        $product->sku,
                        $product->description,
                        $product->retail_price,
                        $product->selling_price,
                        $product->stock,
                        $product->status ? 'Active' : 'Inactive',
                        $product->is_digital_product ? 'Yes' : 'No',
                        $product->is_featured ? 'Yes' : 'No',
                        $product->is_special_offer ? 'Yes' : 'No',
                        $product->allow_customer_remarks ? 'Yes' : 'No',
                        $product->weight,
                        $product->dimension,
                        $product->minimum_order_quantity,
                        $product->created_at?->format(config('tables.date_time_format')),
                    ]
                ),
            Actions\CreateAction::make(),
        ];
    }
}
