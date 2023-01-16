<?php

declare(strict_types=1);

namespace App\Tenancy;

use Aws\S3\S3Client;
use Illuminate\Support\Arr;
use Stancl\Tenancy\Contracts\Tenant;

class BucketManager
{
    protected S3Client $s3Client;

    public function __construct(
        protected readonly Tenant $tenant
    ) {
        $this->s3Client = $this->makeS3Client();
    }

    public function makeS3Client(): S3Client
    {
        return new S3Client($this->formatS3Config(config('filesystems.disks.s3')));
    }

    public function formatS3Config(array $config): array
    {
        $config += ['version' => 'latest'];

        if ( ! empty($config['key']) && ! empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return Arr::only($config, ['credentials', 'endpoint', 'region', 'version', 'use_path_style_endpoint']);
    }

    public function createBucket(): void
    {
        $this->s3Client->createBucket(['Bucket' => $this->tenant->getInternal('bucket')]);
    }

    public function deleteBucket(): void
    {
        $this->s3Client->deleteBucket(['Bucket' => $this->tenant->getInternal('bucket')]);
    }
}
