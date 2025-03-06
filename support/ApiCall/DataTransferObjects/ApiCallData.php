<?php

declare(strict_types=1);

namespace Support\ApiCall\DataTransferObjects;

readonly class ApiCallData
{
    public function __construct(
        public string $requestType,
        public string $requestUrl,
        public array $requestResponse,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
