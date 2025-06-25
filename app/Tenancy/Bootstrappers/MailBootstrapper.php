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

    protected string $originalMailDriver;

    protected ?string $originalMailSmtpHost;

    protected null|int|string $originalMailSmtpPort;

    protected ?string $originalMailSmtpUsername;

    protected ?string $originalMailSmtpPassword;

    protected ?string $originalMailSmtpEncryption;

    public function __construct(protected Application $app)
    {
        $this->originalMailFromAddress = $this->app->make('config')['mail.from.address'];
        $this->originalMailFromName = $this->app->make('config')['mail.from.name'];

        // Store the original mail driver
        $this->originalMailDriver = $this->app->make('config')['mail.default'];
        // smtp settings
        $this->originalMailSmtpHost = $this->app->make('config')['mail.mailers.smtp.host'] ?? null;
        $this->originalMailSmtpPort = $this->app->make('config')['mail.mailers.smtp.port'] ?? null;
        $this->originalMailSmtpUsername = $this->app->make('config')['mail..mailers.smtp.username'] ?? null;
        $this->originalMailSmtpPassword = $this->app->make('config')['mail.mailers.smtp.password'] ?? null;
        $this->originalMailSmtpEncryption = $this->app->make('config')['mail.mailers.smtp.encryption'] ?? null;
        // $th

    }

    public function bootstrap(Tenant $tenant): void
    {
        if ($tenant->getInternal('mail_from_address')) {
            $this->app->make('config')->set('mail.from.address', $tenant->getInternal('mail_from_address'));
        }

        /** @phpstan-ignore property.notFound */
        $this->app->make('config')->set('mail.from.name', $tenant->name);

        if ($tenant->getInternal('mail_driver') === 'smtp') {

            $this->app->make('config')->set('mail.default', $tenant->getInternal('mail_driver'));
            $this->app->make('config')->set('mail.mailers.smtp.host', $tenant->getInternal('mail_host'));
            $this->app->make('config')->set('mail.mailers.smtp.port', $tenant->getInternal('mail_port')); // Default to 587 if not set
            $this->app->make('config')->set('mail.mailers.smtp.username', $tenant->getInternal('mail_username') ?? null);
            $this->app->make('config')->set('mail.mailers.smtp.password', $tenant->getInternal('mail_password') ?? null);
            $this->app->make('config')->set('mail.mailers.smtp.encryption', $tenant->getInternal('mail_encryption') ?? null);
        }
    }

    public function revert(): void
    {
        $this->app->make('config')->set('mail.from.address', $this->originalMailFromAddress);
        $this->app->make('config')->set('mail.from.name', $this->originalMailFromName);
        $this->app->make('config')->set('mail.default', $this->originalMailDriver);
        $this->app->make('config')->set('mail.mailers.smtp.host', $this->originalMailSmtpHost);
        $this->app->make('config')->set('mail.mailers.smtp.port', $this->originalMailSmtpPort);
        $this->app->make('config')->set('mail.mailers.smtp.username', $this->originalMailSmtpUsername);
        $this->app->make('config')->set('mail.mailers.smtp.password', $this->originalMailSmtpPassword);
        $this->app->make('config')->set('mail.mailers.smtp.encryption', $this->originalMailSmtpEncryption);
        // Resetting the mail configuration to the original state

    }
}
