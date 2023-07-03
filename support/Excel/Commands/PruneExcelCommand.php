<?php

declare(strict_types=1);

namespace Support\Excel\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PruneExcelCommand extends Command
{
    protected $signature = 'app:excel-prune';

    protected $description = 'Prune dangling imports and exports';

    /** @throws \League\Flysystem\FilesystemException */
    public function handle(): int
    {
        $this->prune('exports/', now()->subMinutes(config('support.excel.export_expires_in_minute')));

        $this->prune('imports/', now()->subMinutes(config('support.excel.import_expires_in_minute')));

        return self::SUCCESS;
    }

    /** @throws \League\Flysystem\FilesystemException */
    protected function prune(string $path, Carbon $datetime): void
    {
        $path = Str::finish(config('support.excel.temporary_files.base_directory'), "/{$path}");

        $storage = Storage::disk(config('support.excel.temporary_files.disk'));

        foreach ($storage->listContents($path, false) as $file) {
            if (
                $file->type() === 'file' &&
                $file->lastModified() < $datetime->getTimestamp()
            ) {
                $storage->delete($file->path());
            }
        }
    }
}
