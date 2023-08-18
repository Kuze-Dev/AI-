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
        public readonly FieldType $type,
    ) {
    }
    public static function fromArray(BlockContent $block_content, string $state_path): self
    {
        if ( ! $data['type'] instanceof FieldType) {
            $data['type'] = FieldType::from($data['type']);
        }

        return new self(
            blueprint_id: $block_content->block?->blueprint?->getKey(),
            model_id: $block_content->getKey(),
            model_type: $block_content->getMorphClass(),
            state_path: $state_path,
            value: $data['value'] ?? [],
            type: $block_content->f
        );
    }
}
