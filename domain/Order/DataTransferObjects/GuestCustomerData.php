<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class GuestCustomerData
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $mobile,
        public readonly string $email,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
