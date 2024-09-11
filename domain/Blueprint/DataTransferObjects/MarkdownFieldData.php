<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Enums\MarkdownButton;
use Illuminate\Support\Str;

class MarkdownFieldData extends FieldData
{
    /**
     * @param  array<string>  $rules
     * @param  array<MarkdownButton>  $buttons
     */
    private function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type = FieldType::MARKDOWN,
        public readonly array $rules = [],
        public readonly bool $translatable = true,
        public readonly array $buttons = [],
        public readonly ?string $helper_text = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        if (! empty($data['buttons'] ?? [])) {
            $data['buttons'] = array_map(
                fn (string|MarkdownButton $value) => ! $value instanceof MarkdownButton
                    ? MarkdownButton::from($value)
                    : $value,
                $data['buttons']
            );
        }

        return new self(
            title: $data['title'],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            rules: $data['rules'] ?? [],
            translatable: isset($data['translatable']) ? $data['translatable'] : true,
            buttons: $data['buttons'] ?? [],
            helper_text: $data['helper_text'] ?? null,
        );
    }
}
