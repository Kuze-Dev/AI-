<?php

declare(strict_types=1);

namespace Domain\Customer\Queries;

use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Illuminate\Database\Eloquent\Builder;

/** @extends \Illuminate\Database\Eloquent\Builder<\Domain\Customer\Models\Customer> */
class CustomerQueryBuilder extends Builder
{
    public function whereActive(): self
    {
        return $this->where('status', Status::ACTIVE);
    }

    public function whereRegistered(): self
    {
        return $this->where('register_status', RegisterStatus::REGISTERED);
    }
}
