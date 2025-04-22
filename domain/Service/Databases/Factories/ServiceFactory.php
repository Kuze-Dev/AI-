<?php

declare(strict_types=1);

namespace Domain\Service\Databases\Factories;

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Service\Enums\BillingCycleEnum;
use Domain\Service\Models\Service;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'retail_price' => $this->faker->numberBetween(1, 99_999),
            'selling_price' => $this->faker->numberBetween(1, 99_999),
            'billing_cycle' => Arr::random(BillingCycleEnum::cases()),
            'due_date_every' => Arr::random(range(1, now()->daysInMonth)),
            'pay_upfront' => $this->faker->boolean(),
            'is_featured' => $this->faker->boolean(),
            'is_special_offer' => $this->faker->boolean(),
            'is_subscription' => $this->faker->boolean(),
            'status' => $this->faker->boolean(),
            'needs_approval' => $this->faker->boolean(),
            'is_auto_generated_bill' => $this->faker->boolean(),
            'is_partial_payment' => $this->faker->boolean(),
            //            'is_installment' => $this->faker->boolean(),
        ];
    }

    public function isFeatured(bool $isFeatured = true): self
    {
        return $this->state([
            'is_featured' => $isFeatured,
        ]);
    }

    public function isSpecialOffer(bool $isSpecialOffer = true): self
    {
        return $this->state([
            'is_special_offer' => $isSpecialOffer,
        ]);
    }

    public function isSubscription(bool $isSubscription = true): self
    {
        if ($isSubscription === false) {
            $this->state([
                'billing_cycle' => null,
                'due_date_every' => null,
            ]);
        }

        return $this->state([
            'is_subscription' => $isSubscription,
        ]);
    }

    public function isActive(bool $status = true): self
    {
        return $this->state([
            'status' => $status,
        ]);
    }

    public function needsApproval(bool $needsApproval = true): self
    {
        return $this->state([
            'needs_approval' => $needsApproval,
        ]);
    }

    public function autoGenerateBill(bool $autoGenerateBill = true): self
    {
        return $this->state([
            'is_auto_generated_bill' => $autoGenerateBill,
        ]);
    }

    public function isPartialPayment(bool $isPartialPayment = true): self
    {
        return $this->state([
            'is_partial_payment' => $isPartialPayment,
        ]);
    }

    //    public function isInstallment(bool $isInstallment = true): self
    //    {
    //        return $this->state([
    //            'is_installment' => $isInstallment,
    //        ]);
    //    }

    public function withDummyBlueprint(): self
    {
        return $this->for(BlueprintFactory::new()->withDummySchema());
    }

    public function withTaxonomyTerm(): self
    {
        return $this->hasAttached(TaxonomyTermFactory::new()->for(TaxonomyFactory::new()->withDummyBlueprint()));
    }

    public function withBillingCycle(): self
    {
        $billing_cycle = Arr::random(BillingCycleEnum::cases());

        return $this->state([
            'billing_cycle' => $billing_cycle,
            'due_date_every' => $billing_cycle !== BillingCycleEnum::DAILY
                ? Arr::random(range(1, now()->daysInMonth)) : null,
        ]);
    }
}
