<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class BucketBootstrapper implements TenancyBootstrapper
{
    protected ?string $orignalBucket;
    protected ?string $originalAwsEndpoint;
    protected ?bool $originalUsePathStyleEndpoint;

    public function __construct(protected Application $app)
    {
        $this->orignalBucket = $this->app->make('config')['filesystems.disks.s3.bucket'];

        $this->originalAwsEndpoint = $this->app->make('config')['filesystems.disks.s3.endpoint'];

        $this->originalUsePathStyleEndpoint = $this->app->make('config')['filesystems.disks.s3.use_path_style_endpoint'];
    }

    public function bootstrap(Tenant $tenant): void
    {
        $this->app->make('config')->set('filesystems.disks.s3.bucket', $tenant->getInternal('bucket'));

        if ($tenant->getInternal('aws_endpoint')) {
            $this->app->make('config')->set('filesystems.disks.s3.endpoint', $tenant->getInternal('aws_endpoint'));
            $this->app->make('config')->set('filesystems.disks.s3.use_path_style_endpoint', true);
        }
    }

    public function revert(): void
    {
        $this->app->make('config')->set('filesystems.disks.s3.bucket', $this->orignalBucket);

        $this->app->make('config')->set('filesystems.disks.s3.endpoint', $this->originalAwsEndpoint);

        $this->app->make('config')->set('filesystems.disks.s3.use_path_style_endpoint', $this->originalUsePathStyleEndpoint);
    }
}
