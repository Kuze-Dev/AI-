<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Customer\Events\PasswordResetSent;
use Domain\Tier\Database\Factories\TierFactory;

it('log', function () {
    $customer = CustomerFactory::new()->make([
        'tier_id' => TierFactory::new()->make(),
    ]);

    event(new PasswordResetSent($customer));

    assertActivityLogged(
        event: 'password-reset-sent',
        description: 'Password Reset Sent',
        subject: $customer
    );
});
