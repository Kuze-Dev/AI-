<?php

namespace Domain\Tenant\Bootstrappers;

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
        $this->app = $app;
        $this->orignalDisk = $this->app['config']['filesystems.default'];
        $this->orignalFilamentDisk = $this->app['config']['filament.default_filesystem_disk'];
        $this->orignalFilamentTableDisk = $this->app['config']['tables.default_filesystem_disk'];
        $this->orignalBucket = $this->app['config']['filesystems.disks.s3.bucket'];
    }

    public function bootstrap(Tenant $tenant)
    {
        $this->app['config']->set('filesystems.default', 's3');
        $this->app['config']->set('filament.default_filesystem_disk', 's3');
        $this->app['config']->set('tables.default_filesystem_disk', 's3');
        $this->app['config']->set('filesystems.disks.s3.bucket', $tenant->getInternal('bucket'));
    }

    public function revert()
    {
        $this->app['config']->set('filesystems.default', $this->orignalDisk);
        $this->app['config']->set('filament.default_filesystem_disk', $this->orignalFilamentDisk);
        $this->app['config']->set('tables.default_filesystem_disk', $this->orignalFilamentTableDisk);
        $this->app['config']->set('filesystems.disks.s3.bucket', $this->orignalBucket);
    }
}
