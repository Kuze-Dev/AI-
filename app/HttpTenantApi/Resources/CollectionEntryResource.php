<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $title,
 * @property-read array $data
 * @property-read int $order
 * @property-read \Carbon\Carbon published_at
 */
class CollectionEntryResource extends JsonApiResource
{
    protected function toAttributes(Request $request): array
    {
        return [
            'title' => $this->title,
            'data' => $this->data,
            'order' => $this->order,
            'published_at' => $this->published_at
        ];
    }
}