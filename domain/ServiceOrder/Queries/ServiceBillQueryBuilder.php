<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Queries;

use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/** @extends \Illuminate\Database\Eloquent\Builder<\Domain\ServiceOrder\Models\ServiceBill> */
class ServiceBillQueryBuilder extends Builder
{
    public function wherePendingStatus(): self
    {
        return $this->where('status', ServiceBillStatus::PENDING);
    }

    public function whereStatusPaid(): self
    {
        return $this->where('status', ServiceBillStatus::PAID);
    }

    public function whereHasBillingDate(): self
    {
        return $this->whereNotNull('bill_date');
    }

    public function whereHasDueDate(): self
    {
        return $this->whereNotNull('due_date');
    }

    public function whereServiceOrderRef(string $reference): self
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        $serviceOrder = ServiceOrder::whereReference($reference)->whereCustomerId($customer->id)->first();

        if (! $serviceOrder) {
            return $this;
        }

        return $this->where('service_order_id', $serviceOrder->id);
    }

    public function whereNotifiable(): self
    {
        return $this->wherePendingStatus()
            ->whereHasBillingDate()
            ->whereHasDueDate();
    }
}
