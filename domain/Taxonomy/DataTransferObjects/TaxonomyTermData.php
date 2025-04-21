<?php

declare(strict_types=1);

namespace Domain\Taxonomy\DataTransferObjects;

use Domain\Internationalization\DataTransferObjects\TranslationDTO;

class TaxonomyTermData extends TranslationDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?array $data,
        public readonly bool $is_custom,
        public readonly ?string $url,
        public readonly ?string $translation_id = null,
        public readonly ?int $id = null,
        public readonly ?array $children = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            data: $data['data'] ?? [],
            url: $data['url'] ?? null,
            is_custom: $data['is_custom'] ?? false,
            id: $data['id'] ?? null,
            translation_id: array_key_exists('translation_id', $data) ? (string) $data['translation_id'] : null,
            children: array_map(fn (array $child) => self::fromArray($child), $data['children'] ?? [])
        );
    }
}
