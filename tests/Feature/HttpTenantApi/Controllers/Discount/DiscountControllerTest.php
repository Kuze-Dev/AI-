<?php

declare(strict_types=1);

use Domain\Discount\Database\Factories\DiscountConditionFactory;
use Domain\Discount\Database\Factories\DiscountFactory;
use Domain\Discount\Database\Factories\DiscountRequirementFactory;
use Domain\Discount\Enums\DiscountAmountType;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Enums\DiscountRequirementType;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountCondition;
use Domain\Discount\Models\DiscountRequirement;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\getJson;

beforeEach(fn () => testInTenantContext());

it('can list all available discounts', function () {

    assertDatabaseEmpty(Discount::class);
    assertDatabaseEmpty(DiscountCondition::class);
    assertDatabaseEmpty(DiscountRequirement::class);

    DiscountFactory::new()
        ->has(DiscountConditionFactory::new())
        ->has(DiscountRequirementFactory::new())
        ->count(5)
        ->create();

    getJson('api/discounts?'.http_build_query(['include' => 'discountCondition', 'discountRequirement']))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('included', 5)
                ->whereAll([
                    'included.0.type' => 'discountConditions',
                    'included.1.type' => 'discountRequirement',
                ])
                ->whereType('included.0.attributes.discount_type', 'string')
                ->whereType('included.0.attributes.amount_type', 'string')
                ->whereType('included.0.attributes.amount', 'integer')
                ->whereType('included.1.attributes.requirement_type', 'string')
                ->whereType('included.1.attributes.minimum_amount', 'integer')
                ->etc();
        });
})->only();
