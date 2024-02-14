<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Jobs\CustomerSendInvitationJob;
use Domain\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

readonly class SendRegisterInvitationsAction
{
    /**
     * @template TModel as Customer
     *
     * @param  Collection<int, TModel>|null  $records
     * @param  array<RegisterStatus>  $registerStatuses
     */
    public function execute(?Collection $records = null, array $registerStatuses = []): void
    {
        dispatch(new CustomerSendInvitationJob(
            records: $records,
            registerStatuses: $registerStatuses
        ));
    }
}
