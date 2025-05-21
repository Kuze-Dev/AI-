<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Illuminate\Http\Request;
use InvalidArgumentException;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Menu\Models\Node
 */
class NodeResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'label' => $this->label,
            'url' => $this->url,
            'target' => $this->target,
            'type' => $this->type->value,
            'order' => $this->order,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {

        return [
            'children' => fn () => NodeResource::collection($this->children),
            'model' => function () {
                if (! is_null($this->model)) {
                    return match ($this->model::class) {
                        Page::class => PageResource::make($this->model),
                        Content::class => ContentResource::make($this->model),
                        ContentEntry::class => ContentEntryResource::make($this->model),
                        default => throw new InvalidArgumentException('No resource found for model '.$this->model::class),
                    };
                }

                return EmptyResource::make($this->model);

            },
        ];

    }
}
