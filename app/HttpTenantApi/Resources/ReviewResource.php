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
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'rating' => $this->rating,
            'comment' => $this->comment,
            'customer_name' => $this->is_anonymous ? '*' : $this->customer_name,
            'customer_email' => $this->is_anonymous ? '*' : $this->customer_email,
            'is_anonymous' => $this->is_anonymous,
            'data' => $this->data,
            'like_count' => $this->review_likes->count(),
            'created_at' => $this->created_at,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        $relationships = [
            $this->is_anonymous || is_null($this->customer) ? '' :
                'customer' => fn () => new CustomerResource($this->customer),
            'product' => fn () => new ProductResource($this->product),
            'order' => fn () => new OrderResource($this->product),
            'order_line' => fn () => new OrderLineResource($this->order_line),
        ];

        if (isset($this->media)) {
            $relationships['media'] = fn () => MediaResource::collection($this->media);
        }

        return $relationships;
    }
}
