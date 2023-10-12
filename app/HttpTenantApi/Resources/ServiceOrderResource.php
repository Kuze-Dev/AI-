<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin ServiceOrder
 */
class ServiceOrderResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'additional_charges' => $this->additional_charges,
            'service_name' => $this->service_name,
            'service_price' => $this->service_price,
            // 'service_address' => $this->service_address,
            // 'billing_address' => $this->billing_address,
            'currency_symbol' => $this->currency_symbol,
            'form' => $this->customer_form,
            'schedule' => $this->schedule,
            'total_price' => $this->total_price,
        ];
    }

    /**
     * @param Request $request
     * @return array<string, callable>
     */
    public function toRelationships(Request $request): array
    {
        return [

        ];
    }
}
