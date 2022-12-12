<?php

namespace Domain\Tenant\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class BucketBootstrapper implements TenancyBootstrapper
{
    protected string $orignalDriver;

    protected string $orignalBucket;

    public function __construct(protected Application $app)
    {
        $this->app = $app;
        $this->orignalDriver = $this->app['config']['filesystems.default'];
        $this->orignalBucket = $this->app['config']['filesystems.disks.s3.bucket'];
    }

    public function bootstrap(Tenant $tenant)
    {
        $this->app['config']->set('filesystems.default', 's3');
        $this->app['config']->set('filesystems.disks.s3.bucket', $tenant->getInternal('bucket'));
    }

    public function revert()
    {
        $this->app['config']->set('filesystems.default', $this->orignalDriver);
        $this->app['config']->set('filesystems.disks.s3.bucket', $this->orignalBucket);
    }
}
