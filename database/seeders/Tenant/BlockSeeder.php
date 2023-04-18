<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\RichtextButton;
use Domain\Page\Database\Factories\BlockFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlockSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->blocks() as $block) {
            BlockFactory::new([
                'name' => $block['name'],
                'component' => Str::studly($block['name']),
            ])
                ->for(
                    $this->createBlueprintFactory($block['blueprint'])
                        ->state(['name' => $block['name'] . ' Block Blueprint'])
                )
                ->create();
        }
    }

    public function createBlueprintFactory(array $blueprint): BlueprintFactory
    {
        $blueprintFactory = BlueprintFactory::new();

        foreach ($blueprint['sections'] as $section) {
            $blueprintFactory = $blueprintFactory->addSchemaSection(['title' => $section['title']]);

            foreach ($section['fields'] as $field) {
                $blueprintFactory = $blueprintFactory->addSchemaField($field);
            }
        }

        return $blueprintFactory;
    }

    protected function blocks(): array
    {
        return [
            [
                'name' => 'Text with Heading',
                'blueprint' => [
                    'sections' => [
                        [
                            'title' => 'Main',
                            'fields' => [
                                [
                                    'title' => 'Heading',
                                    'type' => FieldType::TEXT,
                                    'rules' => ['required', 'string'],
                                ],
                                [
                                    'title' => 'Content',
                                    'type' => FieldType::RICHTEXT,
                                    'rules' => ['required', 'string'],
                                    'buttons' => RichtextButton::cases(),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Image with Heading',
                'blueprint' => [
                    'sections' => [
                        [
                            'title' => 'Main',
                            'fields' => [
                                [
                                    'title' => 'Heading',
                                    'type' => FieldType::TEXT,
                                    'rules' => ['required', 'string'],
                                ],
                                [
                                    'title' => 'Image',
                                    'type' => FieldType::FILE,
                                    'rules' => ['required', 'image'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Two Column Text with Heading',
                'blueprint' => [
                    'sections' => [
                        [
                            'title' => 'Main',
                            'fields' => [
                                [
                                    'title' => 'Heading',
                                    'type' => FieldType::TEXT,
                                    'rules' => ['required', 'string'],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Left',
                            'fields' => [
                                [
                                    'title' => 'Content',
                                    'type' => FieldType::RICHTEXT,
                                    'rules' => ['required', 'string'],
                                    'buttons' => RichtextButton::cases(),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Right',
                            'fields' => [
                                [
                                    'title' => 'Content',
                                    'type' => FieldType::RICHTEXT,
                                    'rules' => ['required', 'string'],
                                    'buttons' => RichtextButton::cases(),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Left Text Right Image',
                'blueprint' => [
                    'sections' => [
                        [
                            'title' => 'Left',
                            'fields' => [
                                [
                                    'title' => 'Heading',
                                    'type' => FieldType::TEXT,
                                    'rules' => ['required', 'string'],
                                ],
                                [
                                    'title' => 'Content',
                                    'type' => FieldType::TEXTAREA,
                                    'rules' => ['required', 'string'],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Right',
                            'fields' => [
                                [
                                    'title' => 'Image',
                                    'type' => FieldType::FILE,
                                    'rules' => ['required', 'image'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Left Image Right Text',
                'blueprint' => [
                    'sections' => [
                        [
                            'title' => 'Left',
                            'fields' => [
                                [
                                    'title' => 'Image',
                                    'type' => FieldType::FILE,
                                    'rules' => ['required', 'image'],
                                ],
                            ],
                        ],
                        [
                            'title' => 'Right',
                            'fields' => [
                                [
                                    'title' => 'Heading',
                                    'type' => FieldType::TEXT,
                                    'rules' => ['required', 'string'],
                                ],
                                [
                                    'title' => 'Content',
                                    'type' => FieldType::RICHTEXT,
                                    'rules' => ['required', 'string'],
                                    'buttons' => RichtextButton::cases(),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
