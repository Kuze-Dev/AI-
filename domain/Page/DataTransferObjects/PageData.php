<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Domain\Page\Enums\Visibility;
use Illuminate\Support\Carbon;
use Support\MetaData\DataTransferObjects\MetaDataData;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;

readonly class PageData
{
    public function __construct(
        public string $name,
        public ?string $locale,
        public RouteUrlData $route_url_data,
        public MetaDataData $meta_data,
        public ?int $author_id = null,
        public Visibility $visibility = Visibility::PUBLIC,
        public ?Carbon $published_at = null,
        public bool $published_draft = false,
        public array $block_contents = [],
        public array $sites = []
    ) {}

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
