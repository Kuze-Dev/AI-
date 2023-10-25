<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Queries;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Builder;

/** @extends \Illuminate\Database\Eloquent\Builder<\Domain\ServiceOrder\Models\ServiceOrder> */
class ServiceOrderQueryBuilder extends Builder
{
    public function whereActive(): self
    {
        return $this->where('status', ServiceOrderStatus::ACTIVE);
    }

    public function whereSubscriptionBased(): self
    {
        return $this->whereHas('service', function ($nestedQuery) {
            $nestedQuery->where('is_subscription', true);
        });
    }

    public function whereAutoGenerateBills(): self
    {
        return $this->whereHas('service', function ($nestedQuery) {
            $nestedQuery->where('is_auto_generated_bill', true);
        });
    }

    public function whereShouldAutoGenerateBill(): self
    {
        return $this->whereActive()
            ->whereSubscriptionBased()
            ->whereAutoGenerateBills();
    }
}
