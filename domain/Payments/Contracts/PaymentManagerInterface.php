<?php

declare(strict_types=1);

namespace Domain\Payments\Contracts;

interface PaymentManagerInterface
{
    public function getDefaultDriver(): string;
}
