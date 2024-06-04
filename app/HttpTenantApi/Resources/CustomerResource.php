<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Customer\Models\Customer
 */
class CustomerResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'cuid' => $this->cuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'username' => $this->username,
            'mobile' => $this->mobile,
            'gender' => $this->gender,
            'status' => $this->status,
            'tier_id' => $this->tier_id,
            'birth_date' => $this->birth_date?->toDateString(),
            'is_verified' => $this->hasVerifiedEmail(),
            'data' => $this->data,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->media),
            'addresses' => fn () => AddressResource::collection($this->addresses),
        ];
    }
}
