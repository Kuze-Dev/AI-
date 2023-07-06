<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\DiscountResource\Pages\CreateDiscount;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountCondition;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render discounts', function () {
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
            // 'valid_end_at' => now()->subDay()->addDay()->format('Y-m-d'),

            'discountCondition.discount_type' => 'order_sub_total',
            'discountCondition.amount_type' => 'percentage',
            'discountCondition.amount' => 50,
            'discountRequirement.requirement_type' => 'minimimun_order_amount',
            'discountRequirement.minimum_amount' => 1000,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Discount::class, [
        'id' => 1,
        'name' => 'discount name',
        'slug' => 'discount-name',
        'description' => 'this is the description',
        'code' => 'discount-code',
        'max_uses' => 10,
        'status' => DiscountStatus::ACTIVE->value,
        'valid_start_at' => $valid_start_at,
        // 'valid_end_at' => now()->subDay()->addDay()->toDateString().'00:00:00',
    ]);

    // assertDatabaseHas(DiscountCondition::class, [
    //     'discount_id' => 1,
    //     'discount_type' => 'order_sub_total',
    //     'amount_type' => 'percentage',
    //     'amount' => 50,
    // ]);

    // assertDatabaseHas(DiscountRequirement::class, [
    //     'discount_id' => 1,
    //     'requirement_type' => 'minimimun_order_amount',
    //     'minimum_amount' => 1000,
    // ]);

})->only();
