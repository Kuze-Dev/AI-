<?php

declare(strict_types=1);

use Domain\Blueprint\Actions\UpdateBlueprintAction;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Exceptions\SchemaModificationException;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update blueprint', function () {
    $blueprint = BlueprintFactory::new()->withDummySchema()->createOne();

    $blueprint = app(UpdateBlueprintAction::class)->execute($blueprint, new BlueprintData(
        name: 'Foo',
        schema: $newSchema = SchemaData::fromArray([
            'sections' => [
                [
                    'title' => 'Main',
                    'fields' => [
                        [
                            'title' => 'Title',
                            'type' => FieldType::TEXT,
                        ],
                    ],
                ],
            ],
        ])
    ));

    assertDatabaseHas(
        'blueprints',
        [
            'name' => 'Foo',
            'schema' => json_encode((array) $newSchema),
        ]
    );
});

it('throws exception when modifiying an existing field\'s type', function () {
    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField([
            'title' => 'Title',
            'type' => FieldType::TEXT,
        ])
        ->createOne();

    $blueprint = app(UpdateBlueprintAction::class)->execute($blueprint, new BlueprintData(
        name: 'Foo',
        schema: SchemaData::fromArray([
            'sections' => [
                [
                    'title' => 'Main',
                    'state_name' => 'main',
                    'fields' => [
                        [
                            'title' => 'Title',
                            'state_name' => 'title',
                            'type' => FieldType::MARKDOWN,
                        ],
                        [
                            'title' => 'Content',
                            'type' => FieldType::MARKDOWN,
                        ],
                    ],
                ],
            ],
        ])
    ));
})->throws(SchemaModificationException::class);
