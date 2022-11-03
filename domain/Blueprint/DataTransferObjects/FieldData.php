<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

abstract class FieldData implements Arrayable
{
    /** @param array<string> $rules */
    private function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type,
        public readonly array $rules = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        if (!isset($data['state_name'])) {
            $data['state_name'] = Str::snake($data['title']);
        }

        return $data['type']->getFieldDataClass()::fromArray($data);
    }

    public function toArray()
    {
        return (array) $this;
    }
}
