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
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'additional_charges' => $this->additional_charges,
            'service_name' => $this->service_name,
            'service_price' => $this->service_price,
            'currency_symbol' => $this->currency_symbol,
            'form' => $this->customer_form,
            'schema' => $this->schema,
            'schedule' => $this->schedule,
            'tax_total' => $this->tax_total,
            'tax_percentage' => $this->tax_percentage,
            'tax_display' => $this->tax_display,
            'total_price' => $this->total_price,
            'billing_cycle' => $this->billing_cycle,
            'due_date_every' => $this->due_date_every,
            'is_subscription' => $this->is_subscription,
            'is_partial_payment' => $this->is_partial_payment,
            'created_at' => $this->created_at,
            'service_address' => $this->serviceOrderServiceAddress,
            'billing_address' => $this->serviceOrderBillingAddress,
            'bill_date' => $this->latestServiceBill()?->bill_date,
            'due_date' => $this->latestServiceBill()?->due_date,
            'last_payment_date' => $this->latestPaidServiceBill()?->updated_at,
            'last_payment_method' => $this->latestPaymentMethod()?->slug,
            'created_by' => $this->admin?->first_name.' '.$this->admin?->last_name,
            'total_balance' => $this->totalBalance()->formatSimple(),

        ];
    }

    /**
     * @return array<string, callable>
     */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'serviceBills' => fn () => ServiceBillResource::collection($this->serviceBills),
            is_null($this->service) ? '' : 'service' => fn () => new ServiceResource($this->service),
        ];
    }
}
