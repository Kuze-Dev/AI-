<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;

class UpdateServiceOrderStatusData
{
    public function __construct(
        public readonly ?ServiceOrderStatus $status,
    ) {}

    public static function fromRequest(ServiceOrderStatus $status): self
    {
        return new self(status: $status);
    }
}
