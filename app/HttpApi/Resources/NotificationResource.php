<?php

declare(strict_types=1);

namespace App\HttpApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read array $data
 * @property-read string $type
 * @property-read \Illuminate\Support\Carbon|null $read_at
 */
class NotificationResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'data' => $this->data,
            'type' => $this->type,
            'read_at' => $this->read_at,
        ];
    }
}
