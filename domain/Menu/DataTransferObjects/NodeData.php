<?php

declare(strict_types=1);

namespace Domain\Menu\DataTransferObjects;

use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;

class NodeData
{
    public function __construct(
        public readonly string $label,
        public readonly Target $target,
        public readonly NodeType $type,
        public readonly ?int $id = null,
        public readonly ?string $url = null,
        public readonly ?string $model_type = null,
        public readonly ?int $model_id = null,
        public readonly ?array $children = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            label: $data['label'],
            target: Target::from($data['target']),
            type: NodeType::from($data['type']),
            id: $data['id'] ?? null,
            url: $data['url'] ?? null,
            model_type: $data['model_type'] ?? null,
            model_id: $data['model_id'] ?? null,
            children: array_map(fn (array $child) => self::fromArray($child), $data['children'] ?? [])
        );
    }
}
