<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Arr;

class TextareaFieldData extends FieldData
{
    /** @param array<string> $rules */
    private function __construct(
        public readonly string $title,
        public readonly FieldType $type = FieldType::TEXTAREA,
        public readonly array $rules = [],
        public readonly ?int $min_length = null,
        public readonly ?int $max_length = null,
        public readonly ?int $rows = null,
        public readonly ?int $cols = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(...Arr::only($data, ['title', 'rules', 'min_length', 'max_length', 'rows', 'cols']));
    }
}
