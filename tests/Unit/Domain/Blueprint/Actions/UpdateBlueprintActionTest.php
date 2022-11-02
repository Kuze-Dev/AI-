<?php

declare(strict_types=1);

use Domain\Blueprint\Actions\UpdateBlueprintAction;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\SchemaData;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can update blueprint', function () {
    $blueprint = BlueprintFactory::new()->withDummySchema()->createOne();

    $blueprint = app(UpdateBlueprintAction::class)->execute($blueprint, new BlueprintData(
        name: 'Foo',
        schema: $newSchema = SchemaData::fromArray([
            'sections' => [
                'main' => [
                    'title' => 'Main',
                    'fields' => [
                        'foo' => [
                            'title' => 'Foo',
                            'type' => 'text',
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
