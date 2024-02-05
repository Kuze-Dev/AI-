<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlueprintResource\Pages\CreateBlueprint;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\Blueprint;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    // v3 upgrade set context to panels
    Filament::setCurrentPanel(Filament::getPanel('tenant'));
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateBlueprint::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create blueprint', function () {
    livewire(CreateBlueprint::class)
        ->fillForm([
            'name' => 'Test',
            'schema' => [
                'sections' => [
                    [
                        'title' => 'Main',
                        'fields' => [
                            [
                                'title' => 'Foo',
                                'type' => 'text',
                            ],
                        ],
                    ],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Blueprint::class, ['name' => 'Test']);
});

it('can dehydrate rules as array', function ($state) {
    $component = livewire(CreateBlueprint::class)
        ->fillForm([
            'name' => 'Test',
            'schema' => [
                'sections' => [
                    [
                        'title' => 'Main',
                        'fields' => [
                            [
                                'title' => 'Foo',
                                'type' => 'text',
                                'rules' => $state,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $states = $component->instance()->form->getState();

    expect(data_get($states, 'schema.sections.0.fields.0.rules'))->toBeArray();
})->with([
    'when null is given' => null,
    'when array is given' => ['required'],
    'when string is given' => 'required',
    'when pipe(|) delimited string is given' => 'required|string',
]);

it('can show field options', function (FieldType $fieldType, array $fieldOptions) {
    $component = livewire(CreateBlueprint::class)
        ->fillForm([
            'name' => 'Test',
            'schema' => [
                'sections' => [
                    [
                        'title' => 'Main',
                        'fields' => [
                            [
                                'title' => 'Foo',
                                'type' => $fieldType,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $fields = $component->instance()->form->getFlatFields(withHidden: true);

    foreach ($fieldOptions as $fieldOption) {
        $matchedField = $fields["schema.sections.0.fields.0.{$fieldOption}"] ?? null;

        expect($matchedField)->toBeInstanceOf(Field::class);
    }
})->with([
    'datetime field' => [
        'type' => FieldType::DATETIME,
        'field_options' => [
            'min',
            'max',
            'format',
        ],
    ],
    'file field' => [
        'type' => FieldType::FILE,
        'field_options' => [
            'multiple',
            'reorder',
            'min_size',
            'max_size',
        ],
    ],
    'markdown field' => [
        'type' => FieldType::MARKDOWN,
        'field_options' => ['buttons'],
    ],
    'richtext field' => [
        'type' => FieldType::RICHTEXT,
        'field_options' => ['buttons'],
    ],
    'select field' => [
        'type' => FieldType::SELECT,
        'field_options' => ['multiple'],
    ],
    'checkbox field' => [
        'type' => FieldType::CHECKBOX,
        'field_options' => [],
    ],
    'radio field' => [
        'type' => FieldType::RADIO,
        'field_options' => [],
    ],
    'textarea field' => [
        'type' => FieldType::TEXTAREA,
        'field_options' => [
            'min_length',
            'max_length',
            'rows',
            'cols',
        ],
    ],
    'text field' => [
        'type' => FieldType::TEXT,
        'field_options' => [
            'min_length',
            'max_length',
        ],
    ],
    'email field' => [
        'type' => FieldType::EMAIL,
        'field_options' => [
            'min_length',
            'max_length',
        ],
    ],
    'number field' => [
        'type' => FieldType::NUMBER,
        'field_options' => [
            'min',
            'max',
            'step',
        ],
    ],
    'tel field' => [
        'type' => FieldType::TEL,
        'field_options' => [
            'min_length',
            'max_length',
        ],
    ],
    'url field' => [
        'type' => FieldType::URL,
        'field_options' => [
            'min_length',
            'max_length',
        ],
    ],
    'password field' => [
        'type' => FieldType::PASSWORD,
        'field_options' => [
            'min_length',
            'max_length',
        ],
    ],
    'media field' => [
        'type' => FieldType::MEDIA,
        'field_options' => [
            'min_size',
            'max_size',
        ],
    ],
    'tinyeditor field' => [
        'type' => FieldType::TINYEDITOR,
        'field_options' => [
            'min_length',
            'max_length',
        ],
    ],
]);
