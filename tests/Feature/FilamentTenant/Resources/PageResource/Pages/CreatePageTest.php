<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\CreatePage;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Page\Models\Page;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreatePage::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create page', function () {
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});

it('can not create page with same name', function () {
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    PageFactory::new()
        ->createOne(['name' => 'page 1']);

    assertDatabaseCount(Page::class, 1);

    livewire(CreatePage::class)
        ->fillForm([
            'name' => 'page 1',
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});
