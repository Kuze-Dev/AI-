<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin ServiceBill
 */
class ServiceBillResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'additional_charges' => $this->additional_charges,
            'total_amount' => $this->total_amount,
        ];
    }

    /**
     * @return array<string, callable>
     */
    public function toRelationships(Request $request): array
    {
        return [

        ];
    }
}
