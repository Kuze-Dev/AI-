<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\DiscountResource\Pages\ListDiscounts;
use Domain\Discount\Database\Factories\DiscountConditionFactory;
use Domain\Discount\Database\Factories\DiscountFactory;
use Domain\Discount\Database\Factories\DiscountRequirementFactory;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\ForceDeleteAction;
use Filament\Pages\Actions\RestoreAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});
it('can render page', function () {
    livewire(ListDiscounts::class)
        ->assertOk();
});

it('can list discounts', function () {
    $discounts = DiscountFactory::new()
        ->has(DiscountConditionFactory::new())
        ->has(DiscountRequirementFactory::new())
        ->count(3)
        ->create();

    livewire(ListDiscounts::class)
        ->assertCanSeeTableRecords($discounts)
        ->assertOk();
});

it('can soft delete discount', function () {
    $discount = DiscountFactory::new()
        ->has(DiscountConditionFactory::new())
        ->has(DiscountRequirementFactory::new())
        ->createOne();

    livewire(ListDiscounts::class)
        ->callTableAction(DeleteAction::class, $discount)
        ->assertOk();

    assertSoftDeleted($discount);

});

it('can restore discount', function () {
    $discount = DiscountFactory::new()
        ->has(DiscountConditionFactory::new())
        ->has(DiscountRequirementFactory::new())
        ->deleted()
        ->createOne();

    livewire(ListDiscounts::class)
        ->callTableAction(RestoreAction::class, $discount)
        ->assertOk();

    assertNotSoftDeleted($discount);
});

it('can force delete discount', function () {
    $discount = DiscountFactory::new()
        ->has(DiscountConditionFactory::new())
        ->has(DiscountRequirementFactory::new())
        ->deleted()
        ->createOne();

    livewire(ListDiscounts::class)
        ->callTableAction(ForceDeleteAction::class, $discount)
        ->assertOk();

    assertModelMissing($discount);
});
