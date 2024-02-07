<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Discount\Models\Discount
 */
class DiscountResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'code' => $this->code,
            'slug' => $this->slug,
            'status' => $this->status,
            'valid_start_at' => Carbon::parse($this->valid_start_at)->format('Y-m-d'),
            'valid_end_at' => Carbon::parse($this->valid_end_at)->format('Y-m-d'),
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'discountCondition' => fn () => DiscountConditionResource::make($this->discountCondition),
            'discountRequirement' => fn () => DiscountRequirementResource::make($this->discountRequirement),
        ];
    }
}
