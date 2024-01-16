<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Jobs\CustomerSendInvitationJob;

readonly class SendRegisterInvitationsAction
{
    public function execute(array $registerStatuses): void
    {
        dispatch(new CustomerSendInvitationJob(registerStatuses: $registerStatuses));
    }
}
