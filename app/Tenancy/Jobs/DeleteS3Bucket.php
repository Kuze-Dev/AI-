<?php

declare(strict_types=1);

namespace App\Tenancy\Jobs;

use App\Tenancy\BucketManager;
use Domain\Tenant\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteS3Bucket implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected BucketManager $bucketManager;

    public function __construct(
        protected Tenant $tenant
    ) {
        $this->bucketManager = new BucketManager($tenant);
    }

    public function handle(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->bucketManager->deleteBucket();
    }
}
