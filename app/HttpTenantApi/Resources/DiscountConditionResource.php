<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Discount\Models\DiscountCondition
 */
class DiscountConditionResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'discount_type' => $this->discount_type,
            'amount_type' => $this->amount_type,
            'amount' => $this->amount,
        ];
    }
}
