<?php

declare(strict_types=1);

use Domain\Blueprint\Actions\CreateBlueprintAction;
use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\SchemaData;

use function Pest\Laravel\assertModelExists;

beforeEach(fn () => testInTenantContext());

it('can create blueprint', function () {
    $blueprint = app(CreateBlueprintAction::class)->execute(new BlueprintData(
        name: 'Foo',
        schema: SchemaData::fromArray([
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

    assertModelExists($blueprint);
});
