<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\DiscountResource\Pages\EditDiscount;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Discount\Database\Factories\DiscountConditionFactory;
use Domain\Discount\Database\Factories\DiscountFactory;
use Domain\Discount\Database\Factories\DiscountRequirementFactory;

use Filament\Facades\Filament;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
    CurrencyFactory::new()->createOne([
        'enabled' => true,
    ]);
});

it('can render edit discounts', function () {
    $discount = DiscountFactory::new()
        ->has(DiscountConditionFactory::new())
        ->has(DiscountRequirementFactory::new())
        ->createOne();

    $timezone = Filament::auth()->user()->timezone;

    livewire(EditDiscount::class, ['record' => $discount->getRouteKey()])
        ->assertSuccessful()
        ->assertFormExists()
        ->assertFormSet([
            'name' => $discount->name,
            'slug' => $discount->slug,
            'description' => $discount->description,
            'code' => $discount->code,
            'max_uses' => $discount->max_uses,
            'status' => $discount->status->value,
            'valid_start_at' => $discount->valid_start_at->timezone($timezone)->toDateTimeString(),
            'valid_end_at' => $discount->valid_end_at->timezone($timezone)->toDateTimeString(),

            'discountCondition.discount_type' => $discount->discountCondition->discount_type->value,
            'discountCondition.amount_type' => $discount->discountCondition->amount_type->value,
            'discountCondition.amount' => $discount->discountCondition->amount,
            // 'discountRequirement.requirement_type' => $discount->discountRequirement->requirement_type,
            'discountRequirement.minimum_amount' => $discount->discountRequirement->minimum_amount,
        ])
        ->assertOk();
});
