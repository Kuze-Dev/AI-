<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentExport\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneExportCommand extends Command
{
    protected $signature = 'export-prune';

    protected $description = 'Prune dangling exports';

    /** @throws \League\Flysystem\FilesystemException */
    public function handle(): int
    {
        $datetime = now()->subMinutes(config('filament-export.expires_in_minute'));

        $path = config('filament-export.temporary_files.base_directory');

        $storage = Storage::disk(config('filament-export.temporary_files.disk'));

        foreach ($storage->listContents($path, false) as $file) {
            if (
                $file->type() === 'file' &&
                $file->lastModified() < $datetime->getTimestamp()
            ) {
                $storage->delete($file->path());
            }
        }

        return self::SUCCESS;
    }
}
