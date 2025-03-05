<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

readonly class GuestCustomerData
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $mobile,
        public string $email,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
