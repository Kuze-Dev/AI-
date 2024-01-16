<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Jobs\CustomerSendInvitationJob;
use Illuminate\Database\Eloquent\Collection;

readonly class SendRegisterInvitationsAction
{
    public function execute(?Collection $records = null, array $registerStatuses = []): void
    {
        dispatch(new CustomerSendInvitationJob(
            records: $records,
            registerStatuses: $registerStatuses
        ));
    }
}
