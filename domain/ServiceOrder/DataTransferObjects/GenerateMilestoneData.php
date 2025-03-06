<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Models\ServiceOrder;

class GenerateMilestoneData
{
    public function __construct(
        public readonly ServiceOrder $service_order,
        public readonly array $state,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            service_order: $data['service_order'],
            state: $data['state'],
        );
    }
}
