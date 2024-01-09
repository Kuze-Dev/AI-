<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Domain\Page\Enums\Visibility;
use Illuminate\Support\Carbon;
use Support\MetaData\DataTransferObjects\MetaDataData;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;

class PageData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $locale,
        public readonly RouteUrlData $route_url_data,
        public readonly MetaDataData $meta_data,
        public readonly ?int $author_id = null,
        public readonly Visibility $visibility = Visibility::PUBLIC,
        public readonly ?Carbon $published_at = null,
        public readonly bool $published_draft = false,
        public readonly array $block_contents = [],
        public readonly array $sites = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            locale: $data['locale'] ?? null,
            route_url_data: RouteUrlData::fromArray($data['route_url'] ?? []),
            meta_data: MetaDataData::fromArray($data['meta_data']),
            author_id: $data['author_id'] ?? null,
            visibility: ($data['visibility'] ?? null) instanceof Visibility
                ? $data['visibility']
                : (Visibility::tryFrom($data['visibility'] ?? '') ?? Visibility::PUBLIC),
            published_at: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
            block_contents: array_map(
                fn (array $blockContentData) => new BlockContentData(
                    block_id: $blockContentData['block_id'],
                    data: $blockContentData['data'] ?? null,
                    id: $blockContentData['id'] ?? null,
                ),
                $data['block_contents'] ?? [],
            ),
            published_draft: $data['published_draft'] ?? false,
            sites: $data['sites'] ?? [],
        );
    }
}
