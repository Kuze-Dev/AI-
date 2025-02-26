<?php

declare(strict_types=1);

use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Filament\Forms\Components\Field;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

use Tests\Fixtures\TestComponentWithSchemaFormBuilder;
use function Pest\Livewire\livewire;

it('can render component', function (SchemaData $schema) {
    TestComponentWithSchemaFormBuilder::setSchema($schema);

    $component = livewire(TestComponentWithSchemaFormBuilder::class);

    $fields = $component->instance()->form->getFlatFields(withHidden: false);

    expect($fields['data.main.field'])->toBeInstanceOf(Field::class);
})->with([
    'datetime field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'datetime',
                        'rules' => ['required'],
                        'min' => '2022-11-02',
                        'max' => '2022-12-02',
                        'format' => 'Y/m/d',
                    ],
                ],
            ],
        ],
    ]),
    'file field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'file',
                        'rules' => ['required'],
                        'multiple' => true,
                        'reorder' => true,
                        'accept' => ['image/*'],
                        'min_size' => 1024,
                        'max_size' => 2048,
                        'min_files' => 1,
                        'max_files' => 5,
                    ],
                ],
            ],
        ],
    ]),
    'markdown field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'markdown',
                        'rules' => ['required'],
                        'buttons' => ['attachFiles', 'bold'],
                    ],
                ],
            ],
        ],
    ]),
    'richtext field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'richtext',
                        'rules' => ['required'],
                        'buttons' => ['attachFiles', 'blockquote'],
                    ],
                ],
            ],
        ],
    ]),
    'select field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'select',
                        'rules' => ['required'],
                        'options' => [
                            ['label' => 'Field'],
                            ['label' => 'bar'],
                            ['label' => 'baz'],
                        ],
                        'multiple' => true,
                    ],
                ],
            ],
        ],
    ]),
    'textarea field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'textarea',
                        'rules' => ['required'],
                        'max_length' => 10,
                        'min_length' => 500,
                        'rows' => 8,
                        'cols' => 256,
                    ],
                ],
            ],
        ],
    ]),
    'text field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'text',
                        'rules' => ['required'],
                        'min_length' => 0,
                        'max_length' => 256,
                    ],
                ],
            ],
        ],
    ]),
    'email field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'email',
                        'rules' => ['required'],
                        'min_length' => 0,
                        'max_length' => 256,
                    ],
                ],
            ],
        ],
    ]),
    'number field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'number',
                        'rules' => ['required'],
                        'min' => 0,
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
            ],
        ],
    ]),
    'tel field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'tel',
                        'rules' => ['required'],
                        'min_length' => 0,
                        'max_length' => 256,
                    ],
                ],
            ],
        ],
    ]),
    'url field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'url',
                        'rules' => ['required'],
                        'min_length' => 0,
                        'max_length' => 256,
                    ],
                ],
            ],
        ],
    ]),
    'password field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'password',
                        'rules' => ['required'],
                        'min_length' => 0,
                        'max_length' => 256,
                    ],
                ],
            ],
        ],
    ]),
    'toggle field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'toggle',
                        'rules' => ['required'],
                    ],
                ],
            ],
        ],
    ]),
    'tinyeditor field' => SchemaData::fromArray([
        'sections' => [
            [
                'title' => 'Main',
                'fields' => [
                    [
                        'title' => 'Field',
                        'type' => 'tinyeditor',
                        'rules' => ['required'],
                    ],
                ],
            ],
        ],
    ]),
]);
