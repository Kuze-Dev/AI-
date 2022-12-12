<?php

declare(strict_types=1);

namespace Domain\Tenant\Jobs;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
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

        $this->createBucket();
    }

    protected function generateBucketName(): string
    {
        return Str::of(config('app.name'))->lower()->kebab() . '-' . Str::of($this->tenant->name)->lower()->kebab();
    }

    protected function createBucket(): void
    {
        $client = new S3Client([
            "credentials" => new Credentials(
                config('filesystems.disks.s3.key'),
                config('filesystems.disks.s3.secret'),
            ),
            "endpoint" => config('filesystems.disks.s3.endpoint'),
            "region" => config('filesystems.disks.s3.region'),
            "version" => 'latest',
            "use_path_style_endpoint" => config('filesystems.disks.s3.use_path_style_endpoint'),
        ]);

        $client->createBucket(['Bucket' => $this->tenant->getInternal('bucket')]);
    }
}
