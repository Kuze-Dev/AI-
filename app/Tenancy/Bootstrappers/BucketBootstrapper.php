<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class BucketBootstrapper implements TenancyBootstrapper
{
    protected ?string $originalFileSystemDisk;

    protected ?string $orignalBucket;

    protected ?string $originalMediaDisk;

    protected ?string $originalBucketKey;

    protected ?string $originalBucketSecret;

    protected ?string $originalBucketRegion;

    protected ?string $originalBucketUrl;

    protected ?string $originalBucketEndpoint;

    protected ?string $originalHalcyonImportDisk;

    protected ?bool $originalBucketStyleEndpoint;

    protected string $originalS3Visibility;

    public function __construct(protected Application $app)
    {
        $this->originalFileSystemDisk = $this->app->make('config')['filesystems.default'];
        $this->originalMediaDisk = $this->app->make('config')['media-library.disk_name'];

        $this->orignalBucket = $this->app->make('config')['filesystems.disks.s3.bucket'];

        $this->originalHalcyonImportDisk = $this->app->make('config')['filament-import.temporary_files.disk'];

        $this->originalS3Visibility = $this->app->make('config')['filesystems.disks.s3.visibility'];

        $this->originalBucketKey = $this->app->make('config')['filesystems.disks.s3.key'];
        $this->originalBucketSecret = $this->app->make('config')['filesystems.disks.s3.secret'];
        $this->originalBucketRegion = $this->app->make('config')['filesystems.disks.s3.region'];
        $this->originalBucketUrl = $this->app->make('config')['filesystems.disks.s3.url'];
        $this->originalBucketEndpoint = $this->app->make('config')['filesystems.disks.s3.endpoint'];
        $this->originalBucketStyleEndpoint = $this->app->make('config')['filesystems.disks.s3.use_path_style_endpoint'];

    }

    #[\Override]
    public function bootstrap(Tenant $tenant): void
    {
        if ($tenant->getInternal('bucket_driver') === 'r2') {

            $this->app->make('config')->set('media-library.disk_name', $tenant->getInternal('bucket_driver'));
            $this->app->make('config')->set('filesystems.default', $tenant->getInternal('bucket_driver'));
            $this->app->make('config')->set('filament.default_filesystem_disk', $tenant->getInternal('bucket_driver'));

            $this->app->make('config')->set('filesystems.disks.r2.bucket', $tenant->getInternal('bucket'));
            $this->app->make('config')->set('filesystems.disks.r2.key', $tenant->getInternal('bucket_access_key'));
            $this->app->make('config')->set('filesystems.disks.r2.secret', $tenant->getInternal('bucket_secret_key'));
            $this->app->make('config')->set('filesystems.disks.r2.endpoint', $tenant->getInternal('bucket_endpoint'));
            $this->app->make('config')->set('filesystems.disks.r2.url', $tenant->getInternal('bucket_url'));
            $this->app->make('config')->set('filesystems.disks.r2.use_path_style_endpoint', $tenant->getInternal('bucket_style_endpoint'));
        } elseif (
            $tenant->getInternal('bucket_driver') === 's3' &&
            ! is_null($tenant->getInternal('bucket_access_key')) &&
            ! is_null($tenant->getInternal('bucket_secret_key'))
        ) {

            $visibility = $tenant->getInternal('bucket_url') ? 'private' : 'public';

            $this->app->make('config')->set('filesystems.disks.s3.bucket', $tenant->getInternal('bucket'));
            $this->app->make('config')->set('filesystems.disks.s3.key', $tenant->getInternal('bucket_access_key'));
            $this->app->make('config')->set('filesystems.disks.s3.secret', $tenant->getInternal('bucket_secret_key'));
            $this->app->make('config')->set('filesystems.disks.s3.endpoint', $tenant->getInternal('bucket_endpoint'));

            $this->app->make('config')->set('filesystems.disks.s3.visibility', $visibility);
            $this->app->make('config')->set('filesystems.disks.s3.url', $tenant->getInternal('bucket_url'));
            $this->app->make('config')->set('filesystems.disks.s3.use_path_style_endpoint', $tenant->getInternal('bucket_style_endpoint'));
        } else {
            $this->app->make('config')->set('filesystems.disks.s3.bucket', $tenant->getInternal('bucket'));
        }

        $this->app->make('config')->set('filament-import.temporary_files.disk', $tenant->getInternal('bucket_driver'));
    }

    #[\Override]
    public function revert(): void
    {
        $this->app->make('config')->set('filesystems.disks.s3.bucket', $this->orignalBucket);
        $this->app->make('config')->set('media-library.disk_name', $this->originalMediaDisk);
        $this->app->make('config')->set('filesystems.default', $this->originalFileSystemDisk);
        $this->app->make('config')->set('filament.default_filesystem_disk', $this->originalFileSystemDisk);

        $this->app->make('config')->set('filesystems.disks.r2.bucket', null);
        $this->app->make('config')->set('filesystems.disks.r2.key', null);
        $this->app->make('config')->set('filesystems.disks.r2.secret', null);
        $this->app->make('config')->set('filesystems.disks.r2.endpoint', null);
        $this->app->make('config')->set('filesystems.disks.r2.url', null);
        $this->app->make('config')->set('filesystems.disks.r2.use_path_style_endpoint', false);

        $this->app->make('config')->set('filesystems.disks.s3.key', $this->originalBucketKey);
        $this->app->make('config')->set('filesystems.disks.s3.secret', $this->originalBucketSecret);
        $this->app->make('config')->set('filesystems.disks.s3.endpoint', $this->originalBucketEndpoint);
        $this->app->make('config')->set('filesystems.disks.s3.url', $this->originalBucketUrl);
        $this->app->make('config')->set('filesystems.disks.s3.use_path_style_endpoint', $this->originalBucketStyleEndpoint);
        $this->app->make('config')->set('filesystems.disks.s3.visibility', $this->originalS3Visibility);
        $this->app->make('config')->set('filament-import.temporary_files.disk', $this->originalHalcyonImportDisk);
    }
}
