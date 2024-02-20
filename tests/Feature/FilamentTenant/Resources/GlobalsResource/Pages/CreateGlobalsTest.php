<?php

declare(strict_types=1);

use App\Features\CMS\SitesManagement;
use App\FilamentTenant\Resources\GlobalsResource\Pages\CreateGlobals;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Globals\Database\Factories\GlobalsFactory;
use Domain\Globals\Models\Globals;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Site\Database\Factories\SiteFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();

    LocaleFactory::createDefault();
});

it('can render globals', function () {
    livewire(CreateGlobals::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create globals', function () {
    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField([
            'title' => 'Title',
            'type' => FieldType::TEXT,
        ])
        ->createOne();

    livewire(CreateGlobals::class)
        ->fillForm([
            'blueprint_id' => $blueprint->getKey(),
            'name' => 'Test',
            'data' => ['main' => ['title' => 'Foo']],
        ])->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Globals::class, [
        'name' => 'Test',
        'slug' => 'test',
        'blueprint_id' => $blueprint->getKey(),
        'data' => json_encode(['main' => ['title' => 'Foo']]),
    ]);
});

it('can create globals with same name on microsite', function () {

    activateFeatures(SitesManagement::class);

    SiteFactory::new()->count(2)->create();

    $globals = GlobalsFactory::new([
        'name' => 'test',
    ])
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Title', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $globals->sites()->sync([
        '1',
    ]);

    livewire(CreateGlobals::class)
        ->fillForm([
            'blueprint_id' => $globals->blueprint_id,
            'name' => 'test',
            'sites' => [
                '2',
            ],
            'data' => ['main' => ['title' => 'Foo']],
        ])->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseCount(Globals::class, 2);
});

it('cannot create globals with same name on microsite', function () {

    activateFeatures(SitesManagement::class);

    $siteFactory = SiteFactory::new()->create();

    $globals = GlobalsFactory::new([
        'name' => 'test',
    ])
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Title', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $globals->sites()->sync([$siteFactory->id]);

    livewire(CreateGlobals::class)
        ->fillForm([
            'blueprint_id' => $globals->blueprint_id,
            'name' => 'test',
            'sites' => [
                $siteFactory->id,
            ],
            'data' => ['main' => ['title' => 'Foo']],
        ])->call('create');

    assertDatabaseCount(Globals::class, 1);

});
