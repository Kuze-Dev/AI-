<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Blueprint\Enums\FieldType;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Blueprint\Models\BlueprintData
 */
class BlueprintDataResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        $this->loadMissing('media');

        $data = [
            'value' => $this->value,
            'state_path' => $this->state_path,
            'type' => $this->type,
        ];

        if ($this->type == FieldType::MEDIA->value) {
            $data['media'] = MediaResource::collection($this->media);
        }

        return $data;
    }
}
