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

    protected array $files;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->files as $filePath) {
            if (Storage::disk(config('filament.default_filesystem_disk'))->exists($filePath)) {
                Storage::disk(config('filament.default_filesystem_disk'))->delete($filePath);
            }
        }
    }
}
