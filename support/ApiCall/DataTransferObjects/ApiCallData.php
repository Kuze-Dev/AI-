<?php

declare(strict_types=1);

namespace Support\ApiCall\DataTransferObjects;

class ApiCallData
{
    public function __construct(
        public readonly string $requestType,
        public readonly string $requestUrl,
        public readonly array $requestResponse,
    ) {
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
