<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Clients;

use Illuminate\Support\Facades\Log;

abstract class BaseClient
{
    public function __construct(
        protected readonly Client $client
    ) {
    }

    abstract public static function uri(): string;

    protected static function throwError(array $array): void
    {
        if (isset($array['Error'])) {
            Log::error('error', $array);
            abort(422, 'Something wrong.');
        }
    }
}
