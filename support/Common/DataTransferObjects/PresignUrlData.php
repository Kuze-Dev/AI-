<?php

declare(strict_types=1);

namespace Support\Common\DataTransferObjects;

class PresignUrlData
{
    public function __construct(
        public readonly string $presigned_url,
        public readonly string $object_key,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
