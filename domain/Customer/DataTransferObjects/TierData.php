<?php

declare(strict_types=1);

namespace Domain\Customer\DataTransferObjects;

class TierData
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
    ) {
    }
}
