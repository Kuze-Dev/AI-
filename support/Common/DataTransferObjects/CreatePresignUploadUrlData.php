<?php

declare(strict_types=1);

namespace Support\Common\DataTransferObjects;

class CreatePresignUploadUrlData
{
    public function __construct(
        public readonly string $ext,
        public readonly string $acl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ext: $data['ext'],
            acl: $data['acl'],
        );
    }
}
