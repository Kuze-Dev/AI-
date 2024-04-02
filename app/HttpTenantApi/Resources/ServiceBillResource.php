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
    #[\Override]
    public function toAttributes(Request $request): array
    {

        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'bill_date' => $this->bill_date,
            'sub_total' => $this->sub_total,
            'tax_display' => $this->tax_display,
            'tax_percentage' => $this->tax_percentage,
            'tax_total' => $this->tax_total,
            'additional_charges' => $this->additional_charges,
            'total_amount' => $this->total_amount,
            'payment_method' => $this->paymentMethod()?->slug,
        ];
    }

    /**
     * @return array<string, callable>
     */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'serviceOrder' => fn () => new ServiceOrderResource($this->serviceOrder),
        ];
    }
}
