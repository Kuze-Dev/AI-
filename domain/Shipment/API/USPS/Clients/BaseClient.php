<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Clients;

use Exception;
use Illuminate\Support\Facades\Log;

abstract class BaseClient
{
    public function __construct(
        protected readonly Client $client
    ) {}

    abstract public static function uri(): string;

    protected static function throwError(array $array, string $methodCall): void
    {
        if (isset($array['Error'])) {
            Log::error('Error on '.$methodCall, $array);
            if (app()->isLocal()) {
                throw new Exception($methodCall.': '.json_encode($array));
            } else {
                abort(422, 'Something wrong.');
            }
        }
    }
}
