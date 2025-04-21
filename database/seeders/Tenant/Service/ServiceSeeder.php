<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Service;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Models\Blueprint;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Taxonomy\Models\Taxonomy;
use Illuminate\Database\Seeder;
use Support\MetaData\Database\Factories\MetaDataFactory;

class ServiceSeeder extends Seeder
{
    /** Run the database seeds. */
    //    public function run(): void
    //    {
    //        $this->seedTaxonomy();
    //        ServiceFactory::new([
    //
    //        ])
    //            ->has(MetaDataFactory::new())
    //            ->create();
    //    }
    //
    //    public function seedTaxonomy(): void
    //    {
    //        $blueprintId = Blueprint::whereName($this->data()['blueprint_for_taxonomy']['name'])->value('id');
    //        $blueprintId = $blueprintId !== null ? $blueprintId : BlueprintFactory::new($this->data()['blueprint_for_taxonomy'])->create()->id ?? null;
    //
    //        foreach ($this->data()['taxonomies'] as $taxonomy) {
    // //            $this->ifExists($taxonomy['name']);
    //
    //            TaxonomyFactory::new(
    //                [
    //                    'name' => $taxonomy['name']]
    //            )
    //                ->setBlueprintId($blueprintId)
    //                ->has(
    //                    TaxonomyTermFactory::new($taxonomy['term'])
    //                )->create();
    //        }
    //
    //    }
    //
    //    public function data(): array
    //    {
    //        return [
    //            'services' => [
    //                [
    //                    'name' => 'Cleaning',
    //                    'description' => 'This is cleaning services',
    //                    'price' => 99,
    //                    'image_url' => 'https://www.google.com/imgres?imgurl=https%3A%2F%2Fimg.freepik.com%2Ffree-vector%2Fposter-template-house-cleaning-services-with-various-cleaning-items_1416-1251.jpg%3Fw%3D2000&tbnid=hRO-HYn7sykA3M&vet=12ahUKEwik59-0uLiBAxWg6DgGHfe1ChwQMygHegUIARCDAQ..i&imgrefurl=https%3A%2F%2Fwww.freepik.com%2Ffree-vector%2Fposter-template-house-cleaning-services-with-various-cleaning-items_3469266.htm&docid=s7zp-799Hrn6gM&w=2000&h=2000&q=cleaning%20services&ved=2ahUKEwik59-0uLiBAxWg6DgGHfe1ChwQMygHegUIARCDAQ',
    //                    'data' => [],
    //
    //                ],
    //            ],
    //            'blueprint_for_taxonomy' => [
    //                'name' => 'Image with Heading Block Blueprint',
    //                'schema' => [
    //                    'sections' => [
    //                        [
    //
    //                            'title' => 'Main',
    //                            'fields' => [
    //                                [
    //                                    'max' => null,
    //                                    'min' => null,
    //                                    'step' => null,
    //                                    'type' => 'text',
    //                                    'rules' => [
    //                                        'required',
    //                                        'string',
    //                                    ],
    //                                    'title' => 'Heading',
    //                                    'max_length' => null,
    //                                    'min_length' => null,
    //                                    'state_name' => 'heading',
    //                                ],
    //                                [
    //                                    'type' => 'file',
    //                                    'rules' => [
    //                                        'required',
    //                                        'image',
    //                                    ],
    //                                    'title' => 'Image',
    //                                    'accept' => [],
    //                                    'reorder' => false,
    //                                    'max_size' => null,
    //                                    'min_size' => null,
    //                                    'multiple' => false,
    //                                    'max_files' => null,
    //                                    'min_files' => null,
    //                                    'state_name' => 'image',
    //                                ],
    //                            ],
    //                            'state_name' => 'main',
    //                        ],
    //                    ],
    //                ],
    //            ],
    //            'taxonomies' => [
    //                [
    //                    'name' => 'Services',
    //                    'term' => [
    //                        'name' => 'Service One',
    //                        'data' => [
    //                            'main' => [
    //                                'heading' => 'Service One',
    //                            ],
    //                        ],
    //                    ],
    //                ],
    //            ],
    //        ];
    //    }

    //    public function ifExists(string $name): void
    //    {
    //        Taxonomy::whereName($name)->first()->delete();
    //    }
}
