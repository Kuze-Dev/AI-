<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class MailBootstrapper implements TenancyBootstrapper
{
    protected ?string $originalMailFromAddress;

    protected ?string $originalMailFromName;

    public function __construct(protected Application $app)
    {
        $this->originalMailFromAddress = $this->app->make('config')['mail.from.address'];
        $this->originalMailFromName = $this->app->make('config')['mail.from.name'];

    }

    public function bootstrap(Tenant $tenant): void
    {
        if ($tenant->getInternal('mail_from_address')) {
            $this->app->make('config')->set('mail.from.address', $tenant->getInternal('mail_from_address'));
        }

        /** @phpstan-ignore-next-line */
        $this->app->make('config')->set('mail.from.name', $tenant->name);

    }

    public function revert(): void
    {
        $this->app->make('config')->set('mail.from.address', $this->originalMailFromAddress);
        $this->app->make('config')->set('mail.from.name', $this->originalMailFromName);

    }
}
