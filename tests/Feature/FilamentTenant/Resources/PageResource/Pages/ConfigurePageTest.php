<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\ConfigurePage;
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
    $page = PageFactory::new()->withDummyBlueprint()->createOne();

    livewire(ConfigurePage::class, ['record' => $page->getRouteKey()])
        ->assertFormExists()
        ->assertOk();
});

it('can configure page', function () {
    $page = PageFactory::new()->withDummyBlueprint()->createOne();

    livewire(ConfigurePage::class, ['record' => $page->getRouteKey()])
        ->fillForm(['name' => 'Test'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});

it('can configure page w/ published dates', function () {
    $page = PageFactory::new()->withDummyBlueprint()->createOne();

    livewire(ConfigurePage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'published_dates' => true,
            'past_behavior' => Arr::random(PageBehavior::cases())->value,
            'future_behavior' => Arr::random(PageBehavior::cases())->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});
