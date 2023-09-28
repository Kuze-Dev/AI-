<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class ProductTierDiscountResource extends JsonApiResource
{
    public function toId(Request $request)
    {
        return (string) $this->pivot->id; /** @phpstan-ignore-line */
    }

    public function toType(Request $request)
    {
        return 'productTier';
    }

    public function toAttributes(Request $request): array
    {

        $productTier = $this->pivot; /** @phpstan-ignore-line */

        return  [
            'discount' => $productTier->discount,
            'discount_amount_type' => $productTier->discount_amount_type,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [];
    }
}
