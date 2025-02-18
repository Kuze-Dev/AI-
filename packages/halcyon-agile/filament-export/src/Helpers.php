<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentExport;

use Filament\Facades\Filament;
use Illuminate\Support\Carbon;

final class Helpers
{
    private function __construct()
    {
    }

    public static function fullPath(string $fileName): string
    {
        return config('filament-export.temporary_files.base_directory').DIRECTORY_SEPARATOR.$fileName;
    }

    public static function now(): Carbon
    {
        return now(
            filament_admin_optional()?->{config('filament-export.user_timezone_field')}
        );
    }
}
