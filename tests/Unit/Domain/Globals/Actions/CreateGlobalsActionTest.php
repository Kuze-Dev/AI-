<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Globals\Actions\CreateGlobalsAction;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Domain\Blueprint\Enums\FieldType;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => testInTenantContext());

it('can create globals  ', function () {
    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField([
            'title' => 'Title',
            'type' => FieldType::TEXT,
        ])
        ->createOne();

    assertDatabaseCount(Globals::class, 0);

    app(CreateGlobalsAction::class)
        ->execute(GlobalsData::fromArray([
            'blueprint_id' => $blueprint->getKey(),
            'name' => 'Test',
            'slug' => 'test',
            'data' => ['main' => ['title' => 'Foo']],
        ]));

    assertDatabaseCount(Globals::class, 1);

    assertDatabaseHas(Globals::class, [
        'blueprint_id' => $blueprint->getKey(),
        'name' => 'Test',
        'data' => json_encode(['main' => ['title' => 'Foo']]),
    ]);
});
