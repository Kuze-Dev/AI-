<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

if (! function_exists('filament_admin')) {

    function filament_admin(): Admin
    {
        /** @phpstan-ignore return.type */
        return once(fn () => Filament::auth()->user());
    }
}
