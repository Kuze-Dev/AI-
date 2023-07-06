<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

class CartLineResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'notes_image_url' => $this->getFirstMediaUrl('cart_line_notes')
        ];
    }

    public function toRelationships(Request $request): array
    {
        return [
            'purchasable' => fn () => ProductResource::make($this->purchasable),
            'variant' => fn () => ProductVariantResource::make($this->variant),
        ];
    }
}
