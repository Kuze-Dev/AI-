<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;

abstract class FieldData
{
    /** @param array<string> $rules */
    private function __construct(
        public readonly string $title,
        public readonly FieldType $type,
        public readonly array $rules = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return $data['type']->getFieldDataClass()::fromArray($data);
    }
}
