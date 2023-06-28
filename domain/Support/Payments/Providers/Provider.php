<?php

declare(strict_types=1);

namespace Domain\Support\Payments\Providers;

use Domain\Support\Payments\DataTransferObjects\ProviderData;
use Domain\Support\Payments\Interfaces\PaymentInterface;

abstract class Provider implements PaymentInterface
{
    /** Any config for this payment provider. */
    protected array $config = [];

    protected ProviderData $data;

    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    /** {@inheritDoc} */
    public function withData(ProviderData $data): self
    {
        $this->data = $data;

        return $this;
    }

    /** {@inheritDoc} */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }
}
