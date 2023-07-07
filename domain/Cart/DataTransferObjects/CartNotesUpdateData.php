<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class CartNotesUpdateData
{
    public function __construct(
        public readonly int $cart_line_id,
        public readonly mixed $meta,
        public readonly ?array $files,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cart_line_id: (int) $data['cart_line_id'],
            meta: $data['meta'],
            files: $data['file'] ?? null,
        );
    }
}
