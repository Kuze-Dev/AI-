<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use App\Settings\FormSettings;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Exceptions\NoSenderEmailException;
use Domain\Customer\Models\Customer;
use Domain\Customer\Notifications\RegisterInvitationNotification;

class SendRegisterInvitationAction
{
    public function execute(Customer $customer): bool
    {
        if (! app(FormSettings::class)->sender_email) {
            throw new NoSenderEmailException('No sender email found. Please update your form settings.');
        }

        if (! $customer->isAllowedInvite()) {
            return false;
        }

        $customer->notify(new RegisterInvitationNotification);

        if ($customer->register_status !== RegisterStatus::INVITED) {
            $customer->update([
                'register_status' => RegisterStatus::INVITED,
            ]);
        }

        return true;
    }
}
