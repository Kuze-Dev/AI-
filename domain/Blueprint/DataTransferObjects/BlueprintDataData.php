<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Domain\Page\Models\BlockContent;
use Illuminate\Support\Str;

class BlueprintDataData
{
    public function __construct(
        public readonly int $blueprint_id,
        public readonly int $model_id,
        public readonly string $model_type,
        public readonly string $state_path,
        public readonly ?array $value,
        public readonly FieldType $type = FieldType::MEDIA,
    ) {
    }
    public static function fromArray(array $data, BlockContent $block_content): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(
            blueprint_id: $block_content->block?->blueprint?->getKey(),
            model_id: $block_content->getKey(),
            model_type: $block_content->getMorphClass(),
            state_path: $data['state_path'] ?? (string) Str::of($data['title'])->lower()->snake(),
            value: $data['value'] ?? [],
        );
    }
}
