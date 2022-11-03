<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class OptionData implements Arrayable
{
    /** @param array<SectionData> $sections */
    private function __construct(
        public readonly string $label,
        public readonly string $value
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            label: $data['label'],
            value: $data['value'] ?? Str::snake($data['label']),
        );
    }

    public function toArray()
    {
        return (array) $this;
    }
}
