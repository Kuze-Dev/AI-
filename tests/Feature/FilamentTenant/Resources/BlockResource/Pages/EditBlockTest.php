<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlockResource\Blocks\EditBlock;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Models\Block;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can render page', function () {
    $block = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(EditBlock::class, ['record' => $block->getRouteKey()])
        ->assertSuccessful()
        ->assertFormExists()
        ->assertFormSet([
            'name' => $block->name,
            'component' => $block->component,
            'blueprint_id' => $block->blueprint_id,
        ])
        ->assertOk();
});

it('can edit page', function () {
    $block = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(EditBlock::class, ['record' => $block->getRouteKey()])
        ->fillForm([
            'name' => 'Foo',
            'component' => 'Foo',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Block::class, [
        'name' => 'Foo',
        'component' => 'Foo',
    ]);
});
