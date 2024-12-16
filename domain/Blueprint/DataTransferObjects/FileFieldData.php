<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Str;

class FileFieldData extends FieldData
{
    /**
     * @param  array<string>  $rules
     * @param  array<string>  $accept
     */
    private function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type = FieldType::FILE,
        public readonly array $rules = [],
        public readonly array $hidden_option = [],
        public readonly bool $multiple = false,
        public readonly bool $reorder = false,
        public readonly bool $can_download = false,
        public readonly bool $translatable = true,
        public readonly array $accept = [],
        public readonly ?int $min_size = null,
        public readonly ?int $max_size = null,
        public readonly ?int $min_files = null,
        public readonly ?int $max_files = null,
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
            hidden_option: $data['hidden_option'] ?? [],
            translatable: $data['translatable'] ?? true,
            multiple: $data['multiple'] ?? false,
            reorder: $data['reorder'] ?? false,
            can_download: $data['can_download'] ?? false,
            accept: $data['accept'] ?? [],
            min_size: $data['min_size'] ?? null,
            max_size: $data['max_size'] ?? null,
            min_files: $data['min_files'] ?? null,
            max_files: $data['max_files'] ?? null,
            helper_text: $data['helper_text'] ?? null,
        );
    }
}
