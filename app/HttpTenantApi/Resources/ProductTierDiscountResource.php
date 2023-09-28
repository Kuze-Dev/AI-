<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class ProductTierDiscountResource extends JsonApiResource
{
    public function toType(Request $request)
    {
        return 'productTier';
    }

    public function toAttributes(Request $request): array
    {
        /** @phpstan-ignore-next-line */
        $productTier = $this->pivot;

        return  [
            'discount' => $productTier->discount,
            'discount_amount_type' => $productTier->discount_amount_type,
        ];
    }
}
