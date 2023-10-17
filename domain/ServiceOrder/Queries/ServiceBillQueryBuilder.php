<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Queries;

use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Illuminate\Database\Eloquent\Builder;

/** @extends \Illuminate\Database\Eloquent\Builder<\Domain\ServiceOrder\Models\ServiceBill> */
class ServiceBillQueryBuilder extends Builder
{
    public function whereForPaymentStatus(): self
    {
        return $this->where('status', ServiceBillStatus::FORPAYMENT);
    }

    public function whereHasBillingDate(): self
    {
        return $this->whereNotNull('bill_date');
    }

    public function whereHasDueDate(): self
    {
        return $this->whereNotNull('due_date');
    }

    public function whereNotifiable(): self
    {
        return $this->whereForPaymentStatus()
            ->whereHasBillingDate()
            ->whereHasDueDate();
    }
}
