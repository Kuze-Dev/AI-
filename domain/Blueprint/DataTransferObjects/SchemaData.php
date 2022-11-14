<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
class SchemaData implements Arrayable
{
    /** @param array<SectionData> $sections */
    private function __construct(
        public readonly array $sections
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sections: array_map(
                fn (array $section) => SectionData::fromArray($section),
                $data['sections']
            )
        );
    }

    /** @return array<string, mixed> */
    public function toArray()
    {
        return (array) $this;
    }
}
