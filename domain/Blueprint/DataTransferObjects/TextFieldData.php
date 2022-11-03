<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Arr;

class TextFieldData extends FieldData
{
    /** @param array<string> $rules */
    public function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type,
        public readonly array $rules = [],
        public readonly ?int $min_length = null,
        public readonly ?int $max_length = null,
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly ?float $step = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(...Arr::only($data, ['title', 'state_name', 'type', 'rules', 'min_length', 'max_length', 'min', 'max', 'step']));
    }
}
