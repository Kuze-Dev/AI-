<?php

declare(strict_types=1);

namespace App\Tenancy;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Domain\Tenant\Models\Tenant;
use Illuminate\Support\Arr;
use Livewire\FileUploadConfiguration;

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

    public function bucketExists(): bool
    {
        $result = $this->s3Client->listBuckets();

        $buckets = Arr::pluck($result['Buckets'], 'Name');

        return in_array($this->tenant->getInternal('bucket'), $buckets);
    }

    public function createBucket(): void
    {
        $this->s3Client->createBucket(['Bucket' => $this->tenant->getInternal('bucket')]);
    }

    public function configureBucket(): void
    {
        $bucket = $this->tenant->getInternal('bucket');

        // temporarily uploaded file cleanup from Livewire.
        $this->s3Client->putBucketLifecycleConfiguration([
            'Bucket' => $bucket,
            'LifecycleConfiguration' => [
                'Rules' => [
                    [
                        'Prefix' => FileUploadConfiguration::path(),
                        'Expiration' => ['Days' => 1],
                        'Status' => 'Enabled',
                    ],
                ],
            ],
        ]);

        try {
            $this->s3Client->putBucketCors([
                'Bucket' => $bucket,
                'CORSConfiguration' => [
                    'CORSRules' => [
                        [
                            'AllowedHeaders' => ['*'],
                            'AllowedMethods' => [
                                'PUT',
                                'POST',
                                'DELETE',
                            ],
                            'AllowedOrigins' => $this->tenant->domains->pluck('domain')->map(fn (string $domain) => 'https://' . $domain)->toArray(),
                        ],
                        [
                            'AllowedMethods' => [
                                'GET',
                                'HEAD',
                            ],
                            'AllowedOrigins' => ['*'],
                        ],
                    ],
                ],
            ]);
        } catch (S3Exception $exception) {
            report($exception);
        }
    }

    public function deleteBucket(): void
    {
        $this->s3Client->deleteBucket(['Bucket' => $this->tenant->getInternal('bucket')]);
    }
}
