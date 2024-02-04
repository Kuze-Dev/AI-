<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;

class AboutServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        AboutCommand::add('Disk', fn () => [
            'Default' => config('filesystems.default'),
            'Filament' => config('filament.default_filesystem_disk'),
            'Media Library' => config('media-library.disk_name'),
            'Livewire temporary_file_upload' => config('livewire.temporary_file_upload.disk') ?? 'null',
        ]);

    }
}
