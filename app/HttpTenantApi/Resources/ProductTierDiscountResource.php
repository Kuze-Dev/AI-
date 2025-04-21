<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class ProductTierDiscountResource extends JsonApiResource
{
    #[\Override]
    public function toId(Request $request): string
    {
        if (! isset($this->pivot)) {
            return '';
        }

        return (string) $this->pivot->id;
    }

    #[\Override]
    public function toType(Request $request): string
    {
        return 'productTier';
    }

    #[\Override]
    public function toAttributes(Request $request): array
    {
        if (! isset($this->pivot)) {
            return [];
        }

        return [
            'tier_id' => $this->pivot->tier_id,
            'discount' => $this->pivot->discount,
            'discount_amount_type' => $this->pivot->discount_amount_type,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [];
    }
}
