<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Currency\Models\Currency
 */
class CurrencyResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'code' => $this->code,
            'symbol' => $this->symbol,
            'name' => $this->name,
        ];
    }
}
