<?php

declare(strict_types=1);

namespace App\Providers;

use Composer\InstalledVersions;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;

class AboutServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        AboutCommand::add('Disk', fn () => [
            'Default' => config('filesystems.default'),
            'Filament Table' => config('tables.default_filesystem_disk'),
            'Filament Form' => config('forms.default_filesystem_disk'),
            'Media Library' => config('media-library.disk_name'),
            'Livewire temporary_file_upload' => config('livewire.temporary_file_upload.disk') ?? 'null',
            'Excel Import temporary_files (halcyon-agile package)' => config('filament-import.temporary_files.disk') ?? 'null',
            'Excel Export temporary_files (halcyon-agile package)' => config('filament-export.temporary_files.disk') ?? 'null',
            'Excel temporary_files (maatwebsite/excel)' => config('excel.temporary_files.remote_disk') ?? 'null',
        ]);

        AboutCommand::add('Livewire', fn () => [
            'Version' => InstalledVersions::getPrettyVersion('livewire/livewire'),
        ]);
    }
}
