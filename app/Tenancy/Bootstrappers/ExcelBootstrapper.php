<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class ExcelBootstrapper implements TenancyBootstrapper
{
    protected ?string $originalDisk;

    public function __construct(protected Application $app)
    {
        $this->originalDisk = $this->app->make('config')['filesystems.default'];

    }

    public function bootstrap(Tenant $tenant): void
    {
        if ($tenant->getInternal('bucket_driver') === 'r2') {
            $this->app->make('config')->set('excel.temporary_files.remote_disk', $tenant->getInternal('bucket_driver'));
            $this->app->make('config')->set('filament-export.temporary_files.disk', $tenant->getInternal('bucket_driver'));
            $this->app->make('config')->set('filament-import.temporary_files.disk', $tenant->getInternal('bucket_driver'));

        } else {
            $this->app->make('config')->set('excel.temporary_files.remote_disk', 's3');
        }

    }

    public function revert(): void
    {
        $this->app->make('config')->set('excel.temporary_files.remote_disk', $this->originalDisk);
        $this->app->make('config')->set('filament-export.temporary_files.disk', $this->originalDisk);
        $this->app->make('config')->set('filament-import.temporary_files.disk', $this->originalDisk);

    }
}
