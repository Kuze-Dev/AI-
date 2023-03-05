<?php

declare(strict_types=1);

use Domain\Globals\Models\Globals;
use Domain\Blueprint\Enums\FieldType;
use Domain\Globals\Actions\CreateGlobalsAction;
use Domain\Site\Database\Factories\SiteFactory;
use Domain\Globals\DataTransferObjects\GlobalsData;

use Domain\Blueprint\Database\Factories\BlueprintFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseCount;

beforeEach(fn () => testInTenantContext());

it('can create globals  ', function () {
    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField([
            'title' => 'Title',
            'type' => FieldType::TEXT,
        ])
        ->createOne();

    $site = SiteFactory::new()
        ->createOne();

    assertDatabaseCount(Globals::class, 0);

    $global = app(CreateGlobalsAction::class)
        ->execute(GlobalsData::fromArray([
            'blueprint_id' => $blueprint->getKey(),
            'name' => 'Test',
            'slug' => 'test',
            'data' => ['main' => ['title' => 'Foo']],
            'sites' => [$site->id],
        ]));

    assertDatabaseCount(Globals::class, 1);

    assertDatabaseHas(Globals::class, [
        'blueprint_id' => $blueprint->getKey(),
        'name' => 'Test',
        'data' => json_encode(['main' => ['title' => 'Foo']]),
    ]);

    expect($global->sites->pluck('id'))->toContain($site->id);
});
