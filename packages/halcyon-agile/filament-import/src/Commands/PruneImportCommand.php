<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentImport\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneImportCommand extends Command
{
    public $signature = 'import-prune';

    protected $description = 'Prune dangling imports';

    /** @throws \League\Flysystem\FilesystemException */
    public function handle(): int
    {
        $datetime = now()->subMinutes(config('filament-import.expires_in_minute'));

        $path = config('filament-import.temporary_files.base_directory');

        $storage = Storage::disk(config('filament-import.temporary_files.disk'));

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
