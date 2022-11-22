<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\EditPage;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Models\Page;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsAdmin();
});

it('can render page', function () {
    $page = PageFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne(['data' => ['main' => ['header' => 'Foo']]]);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet(['data' => ['main' => ['header' => 'Foo']]])
        ->assertOk();
});

it('can edit page', function () {
    $page = PageFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne(['data' => ['main' => ['header' => 'Foo']]]);

    $newData = ['main' => ['header' => 'Bar']];

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm(['data' => $newData])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(
        Page::class,
        ['data' => json_encode($newData)]
    );
});
