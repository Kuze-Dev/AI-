<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Review\Models\Review
 */
class ReviewResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'title' => $this->title,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'product_review_images' => $this->getMedia('product_review_images')->toArray(),
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'customer' => fn () => new CustomerResource($this->customer),
            'product' => fn () => new ProductResource($this->product),
            'order' => fn () => new OrderResource($this->product),
        ];
    }
}
