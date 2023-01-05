<?php

declare(strict_types=1);

namespace App\Tenancy\Jobs;

use Domain\Tenant\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class DeleteFrameworkDirectoriesForTenant implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Tenant $tenant
    ) {
    }

    public function handle(): void
    {
        if ( ! config('tenancy.filesystem.suffix_storage_path')) {
            return;
        }

        $this->tenant->run(function ($tenant) {
            $storage_path = storage_path();

            File::deleteDirectory("$storage_path/framework");

            if (File::isEmptyDirectory($storage_path)) {
                File::deleteDirectory($storage_path);
            }
        });
    }
}
