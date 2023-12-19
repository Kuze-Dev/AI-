<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Service\Models\Service;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin Service
 */
class ServiceResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'retail_price' => $this->retail_price,
            'selling_price' => $this->selling_price,
            'billing_cycle' => $this->billing_cycle,
            'due_date_every' => $this->due_date_every,
            'is_featured' => $this->is_featured,
            'is_special_offer' => $this->is_special_offer,
            'is_subscription' => $this->is_subscription,
            'pay_upfront' => $this->pay_upfront,
            'status' => $this->status,
            'needs_approval' => $this->needs_approval,
            'is_auto_generated_bill' => $this->is_auto_generated_bill,
            'is_partial_payment' => $this->is_partial_payment,
            //            'is_installment' => $this->is_installment,
            'service_category' => $this->taxonomyTerms->first()?->name,
        ];
    }

    /**
     * @return array<string, callable>
     */
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->media),
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
        ];
    }
}
