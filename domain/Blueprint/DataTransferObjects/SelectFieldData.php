<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Str;

class SelectFieldData extends FieldData
{
    /**
     * @param  array<string>  $rules
     * @param  array<string, OptionData>  $options
     */
    public function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type = FieldType::SELECT,
        public readonly array $rules = [],
        public readonly array $options = [],
        public readonly bool $multiple = false,
        public readonly bool $translatable = true,
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly ?string $helper_text = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        if (! empty($data['options'] ?? [])) {
            $data['options'] = array_map(
                fn (array $option) => OptionData::fromArray($option),
                $data['options']
            );
        }

        return new self(
            title: $data['title'],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            rules: $data['rules'] ?? [],
            translatable: isset($data['translatable']) ? $data['translatable'] : true,
            options: $data['options'] ?? [],
            multiple: $data['multiple'] ?? false,
            min: $data['min'] ?? null,
            max: $data['max'] ?? null,
            helper_text: $data['helper_text'] ?? null,
        );
    }
}
