<?php

declare(strict_types=1);

namespace Domain\Tenant\DataTransferObjects;

class BucketData
{
    public function __construct(
        public readonly ?string $driver,
        public readonly ?string $access_key,
        public readonly ?string $secret_key,
        public readonly ?string $bucket,
        public readonly ?string $endpoint,
        public readonly ?bool $style_endpoint,
        public readonly ?string $url,
        public readonly ?string $region,
    ) {
    }
}
