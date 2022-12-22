<?php

declare(strict_types=1);

namespace Domain\Menu\DataTransferObjects;

use Domain\Menu\Enums\Target;

class NodeData
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $label,
        public readonly ?int $menu_id = null,
        public readonly ?int $parent_id = null,
        public readonly ?int $sort = null,
        public readonly ?string $url = null,
        public readonly ?Target $target = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        if (!$data['target'] instanceof Target) {
            $data['target'] = Target::from($data['target']);
        }
        return new self(
            label: $data['label'],
            menu_id: $data['id'],
            parent_id: $data['parent_id'],
            sort: $data['sort'],
            url: $data['url'],
            target: $data['target'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray()
    {
        return (array) $this;
    }
}
