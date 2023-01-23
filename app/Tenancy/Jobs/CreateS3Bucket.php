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

    protected BucketManager $bucketManager;

    public function __construct(
        protected Tenant $tenant
    ) {
        $this->bucketManager = new BucketManager($tenant);
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

        if ( ! $this->bucketManager->bucketExists()) {
            $this->bucketManager->createBucket();
        }

        $this->bucketManager->configureBucket();
    }

    protected function generateBucketName(): string
    {
        return Str::of(config('app.name'))->lower()->kebab() . '-' . Str::of($this->tenant->name)->lower()->kebab();
    }
}
