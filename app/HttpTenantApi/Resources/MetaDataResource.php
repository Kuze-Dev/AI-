<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Support\MetaData\Models\MetaData
 */
class MetaDataResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'title' => $this->title,
            'author' => $this->description,
            'keywords' => $this->keywords,
            'description' => $this->description,
        ];
    }
}
