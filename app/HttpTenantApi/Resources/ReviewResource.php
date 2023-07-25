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
            'rating' => $this->rating,
            'comment' => $this->comment,
            'data' => $this->data,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'media' => $this->media,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'product' => fn () => new ProductResource($this->product),
            'order' => fn () => new OrderResource($this->product),
            'order_line' => fn () => new OrderLineResource($this->order_line),
            'media' => fn () => MediaResource::collection($this->media),
        ];
    }
}
