<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Collection;
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

if (! function_exists('super_users')) {
    function super_users(): Collection
    {
        return once(fn () => Admin::role(config('domain.role.super_admin'))->get());
    }
}

if (! function_exists('is_image_url')) {
    function is_image_url(string $path): bool
    {
        return filter_var($path, FILTER_VALIDATE_URL) !== false &&
               preg_match('/\.(jpe?g|png|gif|webp|bmp|svg)$/i', (string) parse_url($path, PHP_URL_PATH));
    }
}
