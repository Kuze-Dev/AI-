<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Admin\Models\Admin;
use Domain\Auth\Enums\EmailVerification;

class SendEmailVerificationAction
{
    public function execute(Admin $user): EmailVerification
    {
        if ($user->hasVerifiedEmail()) {
            return EmailVerification::from('user.verified');
        }

        if ( ! $user->deleted_at == null) {
            return EmailVerification::from('user.invalid');
        }

        $user->sendEmailVerificationNotification();

        return EmailVerification::from('user.verify');
    }
}
