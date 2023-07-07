<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class CartResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'coupon_code' => $this->coupon_code,
        ];
    }

    public function toRelationships(Request $request): array
    {
        return [
            'cartLines' => fn () => CartLineResource::collection($this->cartLines),
        ];
    }
}
