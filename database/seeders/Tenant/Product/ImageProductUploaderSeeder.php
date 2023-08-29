<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Product;

use Domain\Product\Models\Product;
use Illuminate\Database\Seeder;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ImageProductUploaderSeeder extends Seeder
{
    public function run(): void
    {
        $output = $this->command->getOutput();

        $output->info('Attaching image to products ...');

        collect(collect(ProductSeeder::data())
            ->only('products')
            ->first())
            ->map(function (array $data) use ($output): void {
                $product = Product::whereName($data['name'])->first();

                if ($product === null) {
                    $output->error('product not found: '.$data['name']);

                    return;
                }

                try {
                    $product->clearMediaCollection('image');

                    $product
                        ->addMediaFromUrl($data['image_url'])
                        ->toMediaCollection('image');
                } catch (FileDoesNotExist $e) {
                    $output->error('FileDoesNotExist: '.$e->getMessage());
                } catch (FileIsTooBig $e) {
                    $output->error('FileIsTooBig: '.$e->getMessage());
                } catch (FileCannotBeAdded $e) {
                    $output->error('FileCannotBeAdded: '.$e->getMessage());
                }
                $output->success('Done for product: '.$data['name']);

            });

        $output->success('Done attaching image to products!');
        $output->newLine();
    }
}
