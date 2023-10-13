<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Queries;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Builder;

/** @extends \Illuminate\Database\Eloquent\Builder<\Domain\ServiceOrder\Models\ServiceOrder> */
class ServiceOrderQueryBuilder extends Builder
{
    public function scopeWhereActive(): self
    {
        return $this->where('status', ServiceOrderStatus::ACTIVE);
    }

    public function scopeWhereSubscriptionBased(): self
    {
        return $this->whereHas('service', function ($nestedQuery) {
            $nestedQuery->where('is_subscription', true);
        });
    }
}
