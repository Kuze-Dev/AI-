<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class BucketBootstrapper implements TenancyBootstrapper
{
    protected ?string $orignalDisk;

    protected ?string $orignalFilamentDisk;

    protected ?string $orignalFilamentTableDisk;

    protected ?string $orignalBucket;

    public function __construct(protected Application $app)
    {
        $this->orignalDisk = $this->app->make('config')['filesystems.default'];
        $this->orignalFilamentDisk = $this->app->make('config')['filament.default_filesystem_disk'];
        $this->orignalFilamentTableDisk = $this->app->make('config')['tables.default_filesystem_disk'];
        $this->orignalBucket = $this->app->make('config')['filesystems.disks.s3.bucket'];
    }

    public function bootstrap(Tenant $tenant): void
    {
        if ( ! config('tenancy.filesystem.s3.enabled', false)) {
            return;
        }

        $this->app->make('config')->set('filesystems.default', 's3');
        $this->app->make('config')->set('filament.default_filesystem_disk', 's3');
        $this->app->make('config')->set('tables.default_filesystem_disk', 's3');
        $this->app->make('config')->set('filesystems.disks.s3.bucket', $tenant->getInternal('bucket'));
    }

    public function revert(): void
    {
        if ( ! config('tenancy.filesystem.s3.enabled', false)) {
            return;
        }

        $this->app->make('config')->set('filesystems.default', $this->orignalDisk);
        $this->app->make('config')->set('filament.default_filesystem_disk', $this->orignalFilamentDisk);
        $this->app->make('config')->set('tables.default_filesystem_disk', $this->orignalFilamentTableDisk);
        $this->app->make('config')->set('filesystems.disks.s3.bucket', $this->orignalBucket);
    }
}
