<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Form\Models\Form
 */
class FormResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'store_submission' => $this->store_submission,
            'uses_captcha' => $this->uses_captcha,
        ];
    }

    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'blueprint' => fn () => BlueprintResource::make($this->blueprint),
        ];
    }
}
