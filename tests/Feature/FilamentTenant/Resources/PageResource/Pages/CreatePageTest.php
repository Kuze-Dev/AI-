<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\CreatePage;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Models\Page;
use Filament\Facades\Filament;

use Illuminate\Support\Arr;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsAdmin();
});

it('can render page', function () {
    livewire(CreatePage::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create page', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});

it('can not create page with same name', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    PageFactory::new()->createOne([
        'name' => 'page 1',
    ]);

    assertDatabaseCount(Page::class, 1);

    livewire(CreatePage::class)
        ->fillForm([
            'name' => 'page 1',
            'blueprint_id' => $blueprint->getKey(),
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});

it('can create page w/out published dates', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'published_dates' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});

it('can create page w/ published dates', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'blueprint_id' => $blueprint->getKey(),
            'published_dates' => true,
            'past_behavior' => Arr::random(PageBehavior::cases())->value,
            'future_behavior' => Arr::random(PageBehavior::cases())->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});
