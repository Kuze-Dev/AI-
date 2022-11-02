<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Illuminate\Support\Str;

class SectionData
{
    /** @param array<string, FieldData> $fields */
    private function __construct(
        public readonly string $title,
        public readonly array $fields
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            fields: collect($data['fields'])
                ->mapWithKeys(fn (array $field, string|int $key) => [
                    (is_string($key) ? $key : Str::slug($field['title'])) => FieldData::fromArray($field),
                ])
                ->toArray()
        );
    }
}
