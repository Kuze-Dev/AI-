<?php

declare(strict_types=1);

use Domain\Auth\Events\PasswordResetSent;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Tier\Database\Factories\TierFactory;

uses()->group('customer');

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
