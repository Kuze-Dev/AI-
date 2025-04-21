<?php

declare(strict_types=1);

namespace Domain\Menu\DataTransferObjects;

use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;

readonly class NodeData
{
    public function __construct(
        public string $label,
        public Target $target,
        public NodeType $type,
        public ?int $id = null,
        public ?string $url = null,
        public ?string $model_type = null,
        public ?int $model_id = null,
        public ?string $translation_id = null,
        public ?array $children = [],
    ) {}

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
            translation_id: array_key_exists('translation_id', $data) ? (string) $data['translation_id'] : null,
            children: array_map(fn (array $child) => self::fromArray($child), $data['children'] ?? [])
        );
    }
}
