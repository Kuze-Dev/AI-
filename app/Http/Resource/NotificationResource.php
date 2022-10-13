<?php

declare(strict_types=1);

namespace App\Http\Resource;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read array{message:string, type:string} $data
 */
class NotificationResource extends JsonApiResource
{
    protected function toAttributes(Request $request): array
    {
        return [
            'message' => $this->data['message'],
            'type' => $this->data['type'],
        ];
    }
}
