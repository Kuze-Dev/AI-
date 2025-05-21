<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\DiscountResource\Pages\CreateDiscount;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Discount\Enums\DiscountRequirementType;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountCondition;
use Domain\Discount\Models\DiscountRequirement;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
    CurrencyFactory::new()->createOne([
        'enabled' => true,
    ]);
});

it('can render page', function () {
    livewire(CreateDiscount::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create discount', function () {

    livewire(CreateDiscount::class)
        ->fillForm([
            'name' => 'discount name',
            'slug' => 'discount-name',
            'description' => 'this is the description',
            'code' => 'discount-code',
            'max_uses' => 10,
            'status' => DiscountStatus::ACTIVE->value,
            'valid_start_at' => ($valid_start_at = now(Auth::user()->timezone)->toImmutable()),

            'discountCondition.discount_type' => 'order_sub_total',
            'discountCondition.amount_type' => 'percentage',
            'discountCondition.amount' => 50,
            'discountRequirement.requirement_type' => DiscountRequirementType::MINIMUM_ORDER_AMOUNT,
            'discountRequirement.minimum_amount' => 1000,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Discount::class, [
        'name' => 'discount name',
        'slug' => 'discount-name',
        'description' => 'this is the description',
        'code' => 'discount-code',
        'max_uses' => 10,
        'status' => DiscountStatus::ACTIVE->value,
        'valid_start_at' => $valid_start_at,
    ]);

    assertDatabaseHas(DiscountCondition::class, [
        'discount_id' => Discount::first()->getKey(),
        'discount_type' => 'order_sub_total',
        'amount_type' => 'percentage',
        'amount' => 50,
    ]);

    assertDatabaseHas(DiscountRequirement::class, [
        'discount_id' => Discount::first()->getKey(),
        'requirement_type' => 'minimum_order_amount',
        'minimum_amount' => 1000,
    ]);

});
