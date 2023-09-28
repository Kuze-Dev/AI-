<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class ProductTierDiscountResource extends JsonApiResource
{
    public function toId(Request $request): string
    {
        if ( ! isset($this->pivot)) {
            return '';
        }

        return (string) $this->pivot->id;
    }

    public function toType(Request $request): string
    {
        return 'productTier';
    }

    public function toAttributes(Request $request): array
    {
        if ( ! isset($this->pivot)) {
            return [];
        }

        return  [
            'discount' => $this->pivot->discount,
            'discount_amount_type' => $this->pivot->discount_amount_type,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [];
    }
}
