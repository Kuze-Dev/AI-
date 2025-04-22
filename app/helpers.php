<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

if (! function_exists('filament_admin')) {

    function filament_admin(): Admin
    {
        /** @phpstan-ignore return.type */
        return once(fn () => Filament::auth()->user());
    }
}

if (! function_exists('guest_customer_logged_in')) {
    function guest_customer_logged_in(): ?Customer
    {
        /** @phpstan-ignore return.type */
        return once(fn () => Auth::user());
    }
}

if (! function_exists('customer_logged_in')) {
    function customer_logged_in(): Customer
    {
        /** @phpstan-ignore return.type */
        return guest_customer_logged_in();
    }
}
