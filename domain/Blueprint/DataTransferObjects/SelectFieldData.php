<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SelectFieldData extends FieldData
{
    /**
     * @param array<string> $rules
     * @param array<string, OptionData> $options
     */
    public function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type = FieldType::SELECT,
        public readonly array $rules = [],
        public readonly array $options = [],
        public readonly ?bool $multiple = false,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (!$data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        if (!empty($data['options'] ?? [])) {
            $data['options'] = array_map(
                fn (array $option) => OptionData::fromArray($option),
                $data['options']
            );
        }

        return new self(...Arr::only($data, ['title', 'state_name', 'rules', 'options', 'multiple']));
    }
}
