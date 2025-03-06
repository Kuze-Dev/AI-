<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class SectionData implements Arrayable
{
    /** @param  array<int, FieldData>  $fields */
    private function __construct(
        public string $title,
        public string $state_name,
        public array $fields
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            fields: array_map(
                fn (array $field) => FieldData::fromArray($field),
                $data['fields']
            )
        );
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function toArray()
    {
        return (array) $this;
    }
}
