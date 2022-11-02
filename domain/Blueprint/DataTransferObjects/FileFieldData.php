<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;

class FileFieldData extends FieldData
{
    /**
     * @param array<string> $rules
     * @param array<string> $accept
     */
    private function __construct(
        public readonly string $title,
        public readonly FieldType $type = FieldType::FILE,
        public readonly array $rules = [],
        public readonly bool $multiple = false,
        public readonly bool $reorder = false,
        public readonly array $accept = [],
        public readonly ?int $min_size = null,
        public readonly ?int $max_size = null,
        public readonly ?int $min_files = null,
        public readonly ?int $max_files = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(...$data);
    }
}
