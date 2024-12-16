<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\TiptapTools;
use Illuminate\Support\Str;

class TipTapEditorData extends FieldData
{
    /** @param  array<string>  $rules */
    private function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type = FieldType::TIPTAPEDITOR,
        public readonly array $rules = [],
        public readonly array $hidden_option = [],
        public readonly array $accept = [],
        public readonly array $tools = [],
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

        if (! empty($data['tools'] ?? [])) {
            $data['tools'] = array_map(
                fn (string|TiptapTools $value) => ! $value instanceof TiptapTools
                    ? TiptapTools::from($value)
                    : $value,
                $data['tools']
            );
        }

        return new self(
            title: $data['title'],
            accept: $data['accept'] ?? [],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            rules: $data['rules'] ?? [],
            hidden_option: $data['hidden_option'] ?? [],
            tools: $data['tools'] ?? [],
            translatable: $data['translatable'] ?? true,
            min_length: $data['min_length'] ?? null,
            max_length: $data['max_length'] ?? null,
            helper_text: $data['helper_text'] ?? null,
        );
    }
}
