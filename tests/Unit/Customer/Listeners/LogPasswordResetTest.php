<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Tier\Database\Factories\TierFactory;
use Illuminate\Auth\Events\PasswordReset;

it('log', function () {
    $customer = CustomerFactory::new()->make([
        'tier_id' => TierFactory::new()->make(),
    ]);
    event(new PasswordReset($customer));

    assertActivityLogged(
        event: 'password-reset',
        description: 'Password Reset',
        subject: $customer
    );
});
