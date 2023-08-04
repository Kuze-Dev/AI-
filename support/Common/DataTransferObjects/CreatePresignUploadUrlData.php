<?php

declare(strict_types=1);

namespace Support\Common\DataTransferObjects;

class CreatePresignUploadUrlData
{
    public function __construct(
        public readonly string $resource,
        public readonly string $resource_id,
        public readonly string $ext,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            resource: $data['resource'],
            resource_id: $data['resource_id'],
            ext: $data['ext'],
        );
    }
}
