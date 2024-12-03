<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Str;

class MediaFieldData extends FieldData
{
    /**
     * @param  array<string>  $rules
     * @param  array<string>  $accept
     * @param  array<ConversionData>  $conversions
     */
    private function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly FieldType $type = FieldType::MEDIA,
        public readonly array $rules = [],
        public readonly bool $multiple = false,
        public readonly bool $reorder = false,
        public readonly bool $translatable = true,
        public readonly array $accept = [],
        public readonly ?int $min_size = null,
        public readonly ?int $max_size = null,
        public readonly ?int $min_files = null,
        public readonly ?int $max_files = null,
        public readonly ?string $helper_text = null,
        public readonly array $conversions = [],
    ) {
    }

    #[\Override]
    public static function fromArray(array $data): self
    {
        //        if ( ! $data['type'] instanceof FieldType) {
        //            $data['type'] = FieldType::from($data['type']);
        //        }

        return new self(
            title: $data['title'],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            rules: $data['rules'] ?? [],
            multiple: $data['multiple'] ?? false,
            reorder: $data['reorder'] ?? false,
            accept: $data['accept'] ?? [],
            translatable: isset($data['translatable']) ? $data['translatable'] : true,
            min_size: $data['min_size'] ?? null,
            max_size: $data['max_size'] ?? null,
            min_files: $data['min_files'] ?? null,
            max_files: $data['max_files'] ?? null,
            helper_text: $data['helper_text'] ?? null,
            conversions: array_map(
                fn (array $conversion) => ConversionData::fromArray($conversion),
                $data['conversions'] ?? []
            ),
        );
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function toArray()
    {
        return (array) $this;
    }
}
