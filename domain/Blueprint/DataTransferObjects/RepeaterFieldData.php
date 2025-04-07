<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Str;

class RepeaterFieldData extends FieldData
{
    /**
     * @param  array<FieldData>  $fields
     * @param  array<string>  $rules
     */
    public function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly array $fields,
        public readonly FieldType $type = FieldType::REPEATER,
        public readonly array $rules = [],
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly ?int $columns = null,
        public readonly ?string $helper_text = null,
    ) {}

    #[\Override]
    public static function fromArray(array $data): self
    {
        if (! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(
            title: $data['title'],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            type: $data['type'],
            rules: $data['rules'] ?? [],
            columns: $data['columns'] ?? null,
            fields: array_map(
                fn (array $field) => FieldData::fromArray($field),
                $data['fields']
            ),
            min: $data['min'] ?? null,
            max: $data['max'] ?? null,
            helper_text: $data['helper_text'] ?? null,
        );
    }
}
