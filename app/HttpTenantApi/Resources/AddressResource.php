<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Address\Models\Address
 */
class AddressResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'country_code' => $this->state->country->code,
            'label_as' => $this->label_as,
            'address_line_1' => $this->address_line_1,
            'zip_code' => $this->zip_code,
            'state_code' => $this->state->code,
            'state_name' => $this->state->name,
            'city' => $this->city,
            'is_default_shipping' => $this->is_default_shipping,
            'is_default_billing' => $this->is_default_billing,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'state' => fn () => StateResource::make($this->state),
        ];
    }
}
