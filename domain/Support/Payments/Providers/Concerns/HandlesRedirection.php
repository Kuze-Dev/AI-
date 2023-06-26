<?php

namespace Domain\Support\Payments\Providers\Concerns;

trait HandlesRedirection
{
    protected string $transactionId;

    protected string $redirectUrl;

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function redirectUrl(): string
    {
        return $this->redirectUrl;
    }
}