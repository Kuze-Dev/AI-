<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Str;

class RadioFieldData extends FieldData
{
    /**
     * @param  array<string>  $rules
     * @param  array<string, OptionData>  $options
     */
    public function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly array $rules = [],
        public readonly array $hidden_option = [],
        public readonly FieldType $type = FieldType::RADIO,
        public readonly array $options = [],
        public readonly array $descriptions = [],
        public readonly bool $inline = false,
        public readonly bool $translatable = true,
        public readonly ?string $helper_text = null,
    ) {
    }

    #[\Override]
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
            hidden_option: $data['hidden_option'] ?? [],
            options: $data['options'] ?? [],
            descriptions: $data['descriptions'] ?? [],
            inline: $data['inline'] ?? false,
            translatable: $data['translatable'] ?? true,
            helper_text: $data['helper_text'] ?? null,
        );
    }
}
