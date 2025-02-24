<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlueprintResource\Pages\CreateBlueprint;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\Blueprint;
use Filament\Forms\Components\Field;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
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
                                'state_name' => 'foo',
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
    'datetime field' => [FieldType::DATETIME, ['min', 'max', 'format']],
    'file field' => [FieldType::FILE, ['multiple', 'reorder', 'min_size', 'max_size']],
    'markdown field' => [FieldType::MARKDOWN, ['buttons']],
    'richtext field' => [FieldType::RICHTEXT, ['buttons']],
    'select field' => [FieldType::SELECT, ['multiple']],
    'checkbox field' => [FieldType::CHECKBOX, []],
    'radio field' => [FieldType::RADIO, []],
    'textarea field' => [FieldType::TEXTAREA, ['min_length', 'max_length', 'rows', 'cols']],
    'text field' => [FieldType::TEXT, ['min_length', 'max_length']],
    'email field' => [FieldType::EMAIL, ['min_length', 'max_length']],
    'number field' => [FieldType::NUMBER, ['min', 'max', 'step']],
    'tel field' => [FieldType::TEL, ['min_length', 'max_length']],
    'url field' => [FieldType::URL, ['min_length', 'max_length']],
    'password field' => [FieldType::PASSWORD, ['min_length', 'max_length']],
    'media field' => [FieldType::MEDIA, ['min_size', 'max_size']],
    'tinyeditor field' => [FieldType::TINYEDITOR, ['min_length', 'max_length']],
]);
