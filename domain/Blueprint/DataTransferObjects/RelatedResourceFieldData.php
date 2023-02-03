<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Support\Str;

class RelatedResourceFieldData extends FieldData
{
    /**
     * @param array<string> $rules
     * @param array<string, mixed> $relation_scopes
     */
    public function __construct(
        public readonly string $title,
        public readonly string $state_name,
        public readonly string $resource,
        public readonly FieldType $type = FieldType::RELATED_RESOURCE,
        public readonly array $rules = [],
        public readonly bool $multiple = false,
        public readonly array $relation_scopes = [],
        public readonly ?int $min = null,
        public readonly ?int $max = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(
            title: $data['title'],
            state_name: $data['state_name'] ?? (string) Str::of($data['title'])->lower()->snake(),
            type: $data['type'],
            rules: $data['rules'] ?? [],
            resource: $data['resource'],
            multiple: $data['multiple'] ?? false,
            relation_scopes: $data['relation_scopes'] ?? [],
            min: $data['min'] ?? null,
            max: $data['max'] ?? null,
        );
    }
}
