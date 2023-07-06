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
        return  [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'status' => $this->status,
            'birth_date' => $this->birth_date->toDateString(),
            'is_verified' => $this->hasVerifiedEmail(),
        ];
    }
}
