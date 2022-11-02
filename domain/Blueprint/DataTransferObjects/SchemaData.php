<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Illuminate\Support\Str;

class SchemaData
{
    /** @param array<string, SectionData> $sections */
    private function __construct(
        public readonly array $sections
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sections: collect($data['sections'])
                ->mapWithKeys(fn (array $section, string|int $key) => [
                    (is_string($key) ? $key : Str::slug($section['title'])) => SectionData::fromArray($section),
                ])
                ->toArray()
        );
    }
}
