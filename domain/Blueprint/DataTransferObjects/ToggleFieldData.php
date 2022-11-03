<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Arr;

class ToggleFieldData extends FieldData
{
    /** @param array<string> $rules */
    public function __construct(
        public readonly string $title,
        public readonly FieldType $type = FieldType::TOGGLE,
        public readonly array $rules = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(...Arr::only($data, ['title', 'rules']));
    }
}
