<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Favorite\Models\Favorite
 */
class FavoriteResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'customer' => fn () => new CustomerResource($this->customer),
            'product' => fn () => new ProductResource($this->product),
        ];
    }
}
