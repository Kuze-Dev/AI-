<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Discount\Models\DiscountRequirement
 */
class DiscountRequirementResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [

            'requirement_type' => $this->requirement_type,
            'minimum_amount' => $this->minimum_amount,

        ];
    }
}
