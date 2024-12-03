<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Str;

class TinyEditorData extends FieldData
{
    /** @param  array<string>  $rules */
    private function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type = FieldType::TINYEDITOR,
        public readonly array $rules = [],
        public readonly bool $translatable = true,
        public readonly ?int $min_length = null,
        public readonly ?int $max_length = null,
        public readonly ?string $helper_text = null,
    ) {
    }

    #[\Override]
    public static function fromArray(array $data): self
    {
        if (! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(
            title: $data['title'],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            rules: $data['rules'] ?? [],
            translatable: isset($data['translatable']) ? $data['translatable'] : true,
            min_length: $data['min_length'] ?? null,
            max_length: $data['max_length'] ?? null,
            helper_text: $data['helper_text'] ?? null,
        );
    }
}
