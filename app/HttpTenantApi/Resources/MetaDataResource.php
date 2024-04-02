<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Support\MetaData\Models\MetaData
 */
class MetaDataResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        $image = $this->getFirstMedia('image');

        return [
            'title' => $this->title,
            'author' => $this->description,
            'keywords' => $this->keywords,
            'description' => $this->description,
            'image' => $image?->getUrl('original'),
            'image_alt_text' => $image?->getCustomProperty('alt_text'),
        ];
    }
}
