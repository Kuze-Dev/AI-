<?php

declare(strict_types=1);

namespace Domain\Blueprint\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DeleteS3FilesFromDeletedBlueprintDataJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $files) {}

    public function handle(): void
    {
        foreach ($this->files as $filePath) {
            if (Storage::disk(config('filament.default_filesystem_disk'))->exists($filePath)) {
                Storage::disk(config('filament.default_filesystem_disk'))->delete($filePath);
            }
        }
    }
}
