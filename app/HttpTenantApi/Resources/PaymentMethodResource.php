<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\PaymentMethod\Models\PaymentMethod
 */
class PaymentMethodResource extends JsonApiResource
{


    public function toAttributes(Request $request): array
    {
        $image = $this->getFirstMedia('logo');

        return  [
            'name' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'gateway' => $this->gateway,
            'logo' => $image?->getUrl(),
            'status' => $this->status,
            'description' => $this->description,
        ];
    }

}
