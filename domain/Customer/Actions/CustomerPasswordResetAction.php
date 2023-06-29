<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;
use Domain\Customer\Notifications\PasswordHasBeenReset;
use Illuminate\Support\Str;

class CustomerPasswordResetAction
{
    public function execute(Customer $customer, string $password): void
    {
        $customer
            ->fill([
                'password' => $password,
            ])
            ->setRememberToken(Str::random(60));

        $customer->save();

        $customer->notify(new PasswordHasBeenReset());
    }
}
