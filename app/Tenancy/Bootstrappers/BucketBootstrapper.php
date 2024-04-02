<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class BucketBootstrapper implements TenancyBootstrapper
{
    protected ?string $orignalBucket;

    public function __construct(protected Application $app)
    {
        $this->orignalBucket = $this->app->make('config')['filesystems.disks.s3.bucket'];
    }

    #[\Override]
    public function bootstrap(Tenant $tenant): void
    {
        $this->app->make('config')->set('filesystems.disks.s3.bucket', $tenant->getInternal('bucket'));
    }

    #[\Override]
    public function revert(): void
    {
        $this->app->make('config')->set('filesystems.disks.s3.bucket', $this->orignalBucket);
    }
}
