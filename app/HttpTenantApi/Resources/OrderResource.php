<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Order\Models\Order
 */
class OrderResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'reference' => $this->reference,
            'total' => $this->total,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('F d, Y H:i:s'),
        ];
    }
}
