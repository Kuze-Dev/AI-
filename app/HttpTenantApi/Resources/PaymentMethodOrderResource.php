<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\PaymentMethod\Models\PaymentMethod
 */
class PaymentMethodOrderResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'gateway' => $this->gateway,
            'status' => $this->status,
            'description' => $this->description,
            'instruction' => $this->instruction,
            'media' => MediaResource::collection($this->media),
        ];
    }
}
