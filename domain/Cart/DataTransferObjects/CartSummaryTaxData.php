<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

readonly class CartSummaryTaxData
{
    public function __construct(
        public ?int $countryId,
        public ?int $stateId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            countryId: $data['countryId'] ?? null,
            stateId: $data['stateId'] ?? null,
        );
    }
}
