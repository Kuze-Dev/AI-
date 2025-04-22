<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\PaymentMethod\Models\PaymentMethod
 */
class PaymentMethodResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        $image = $this->getFirstMedia('logo');

        return [
            'name' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'gateway' => $this->gateway,
            'logo' => $image?->getUrl(),
            'status' => $this->status,
            'description' => $this->description,
            'instruction' => $this->instruction,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->media),
        ];
    }
}
