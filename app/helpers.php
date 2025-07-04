<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

        $is_url = filter_var($path, FILTER_VALIDATE_URL) !== false &&
            preg_match('/\.(jpe?g|png|gif|webp|bmp|svg)$/i', (string) parse_url($path, PHP_URL_PATH));

        if ($is_url) {
            $checkImage = Http::timeout(5)->head($path);

            if (! ($checkImage->ok() &&
                str_starts_with($checkImage->header('Content-Type'), 'image/'))
            ) {
                throw new Exception('The provided URL is not a valid image URL or the request failed.');
            }

            return true;

        }

        return false;
    }
}
