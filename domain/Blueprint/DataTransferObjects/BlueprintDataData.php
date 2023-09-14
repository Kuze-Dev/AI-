<?php

declare(strict_types=1);

namespace Domain\Blueprint\DataTransferObjects;

use Domain\Blueprint\Enums\FieldType;
use Domain\Page\Models\BlockContent;

class BlueprintDataData
{
    public function __construct(
        public readonly string $blueprint_id,
        public readonly int $model_id,
        public readonly string $model_type,
        public readonly string $state_path,
        public readonly string|array $value,
        public readonly FieldType $type,
    ) {
    }

    public static function fromArray(BlockContent $block_content, string $state_path, FieldType $field_type): self
    {

        $data = $block_content->data;

        $keys = explode('.', $state_path);

        foreach ($keys as $key) {
            if ( ! isset($data[$key])) {
            }

            $data = $data[$key];
        }

        return new self(
            blueprint_id: $block_content->block->blueprint->getKey(),
            model_id: $block_content->getKey(),
            model_type: $block_content->getMorphClass(),
            state_path: $state_path,
            value: $data,
            type: $field_type
        );
    }
}
