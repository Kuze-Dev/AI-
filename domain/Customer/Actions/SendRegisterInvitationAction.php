<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Models\Customer;
use Domain\Customer\Notifications\RegisterInvitationNotification;

class SendRegisterInvitationAction
{
    public function execute(Customer $customer): bool
    {
        if ($customer->register_status === RegisterStatus::REGISTERED) {
            return false;
        }

        $customer->notify(new RegisterInvitationNotification());

        $customer->update([
            'register_status' => RegisterStatus::INVITED,
        ]);

        return true;
    }
}
