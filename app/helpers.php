<?php

declare(strict_types=1);

use Domain\Admin\Models\Admin;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

if (! function_exists('filament_admin')) {

    function filament_admin(): Admin
    {
        return once(fn () => Filament::auth()->user());
    }
}

if (! function_exists('filament_admin_optional')) {

    function filament_admin_optional(): ?Admin
    {
        return once(fn () => Filament::auth()->user());
    }
}
