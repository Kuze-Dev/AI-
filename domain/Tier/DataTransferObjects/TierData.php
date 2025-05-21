<?php

declare(strict_types=1);

namespace Domain\Tier\DataTransferObjects;

readonly class TierData
{
    public function __construct(
        public string $name,
        public string $description,
        public bool $has_approval
    ) {}
}
