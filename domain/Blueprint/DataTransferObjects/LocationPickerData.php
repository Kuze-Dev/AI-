<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Str;

class LocationPickerData extends FieldData
{
    /** @param  array<string>  $rules */
    private function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type = FieldType::LOCATION_PICKER,
        public readonly array $rules = [],
        public readonly array $hidden_option = [],
        public readonly bool $translatable = true,
        public readonly ?string $helper_text = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(
            title: $data['title'],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            rules: $data['rules'] ?? [],
            hidden_option: $data['hidden_option'] ?? [],
            translatable: isset($data['translatable']) ? $data['translatable'] : true,
            helper_text: $data['helper_text'] ?? null,
        );
    }
}
