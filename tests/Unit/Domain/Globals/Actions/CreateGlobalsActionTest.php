<?php

declare(strict_types=1);

use App\Features\CMS\SitesManagement;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Globals\Actions\CreateGlobalsAction;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Domain\Site\Database\Factories\SiteFactory;

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

    app(CreateGlobalsAction::class)
        ->execute(GlobalsData::fromArray([
            'blueprint_id' => $blueprint->getKey(),
            'name' => 'Test',
            'slug' => 'test',
            'data' => ['main' => ['title' => 'Foo']],
        ]));

    assertDatabaseHas(Globals::class, [
        'blueprint_id' => $blueprint->getKey(),
        'name' => 'Test',
        'data' => json_encode(['main' => ['title' => 'Foo']]),
    ]);

});

it('can create globals for micro sites  ', function () {

    activateFeatures(SitesManagement::class);

    loginAsSuperAdmin();

    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField([
            'title' => 'Title',
            'type' => FieldType::TEXT,
        ])
        ->createOne();

    $site = SiteFactory::new()
        ->createOne();

    $global = app(CreateGlobalsAction::class)
        ->execute(GlobalsData::fromArray([
            'blueprint_id' => $blueprint->getKey(),
            'name' => 'Test',
            'slug' => 'test',
            'data' => ['main' => ['title' => 'Foo']],
            'sites' => [$site->id],
        ]));

    assertDatabaseHas(Globals::class, [
        'blueprint_id' => $blueprint->getKey(),
        'name' => 'Test',
        'data' => json_encode(['main' => ['title' => 'Foo']]),
    ]);

    expect($global->sites->pluck('id'))->toContain($site->id);
});
