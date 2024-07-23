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
use Illuminate\Support\Str;

class CreateS3Bucket implements ShouldQueue
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
        if ($this->tenant->getInternal('bucket') === null) {
            $this->tenant->setInternal('bucket', $this->generateBucketName());

            $this->tenant->save();
        }

        if (app()->runningUnitTests()) {
            return;
        }

        if (
            $this->tenant->getInternal('bucket_driver') == 'r2' ||
            ! is_null($this->tenant->getInternal('bucket_access_key')) ||
            ! is_null($this->tenant->getInternal('bucket_secret_key'))
        ) {
            return;
        }

        $bucketManager = new BucketManager($this->tenant);

        if (! $bucketManager->bucketExists()) {
            $bucketManager->createBucket();
        }

        $bucketManager->configureBucket();
    }

    protected function generateBucketName(): string
    {
        return Str::of(config('app.name'))->slug().'-'.Str::of($this->tenant->name)->slug();
    }
}
