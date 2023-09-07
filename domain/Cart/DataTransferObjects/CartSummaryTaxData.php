<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

class CartSummaryTaxData
{
    public function __construct(
        public readonly ?int $countryId,
        public readonly ?int $stateId,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            countryId: $data['countryId'] ?? null,
            stateId: $data['stateId'] ?? null,
        );
    }
}
