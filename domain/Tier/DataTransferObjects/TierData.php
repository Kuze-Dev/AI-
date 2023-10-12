<?php

declare(strict_types=1);

namespace Domain\Tier\DataTransferObjects;

class TierData
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly bool $has_approval
    ) {
    }
}
