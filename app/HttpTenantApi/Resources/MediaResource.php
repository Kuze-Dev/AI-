<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Spatie\MediaLibrary\MediaCollections\Models\Media
 */
class MediaResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'collection_name' => $this->collection_name,
            'file_name' => $this->file_name,
            'custom_properties' => $this->custom_properties,
            'original_url' => $this->getUrl(),
            'generated_conversions' => $this->generatedConversionUrls(),
            'type' => $this->type,
        ];
    }

    private function generatedConversionUrls(): array
    {
        return $this->getGeneratedConversions()
            ->map(
                fn ($status, $generatedConversion) => $this
                    ->getFullUrl($generatedConversion)
            )
            ->toArray();
    }
}
