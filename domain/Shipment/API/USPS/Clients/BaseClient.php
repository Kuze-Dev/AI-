<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\Clients;

use Illuminate\Support\Facades\Log;
use Exception;

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
            $debug = debug_backtrace()[0];
            $fileLine = $debug['file'].':'.$debug['line'];
            Log::error('Error on '.$fileLine, $array);
            if (app()->isLocal()) {
                throw new Exception($fileLine.': '.json_encode($array));
            } else {
                abort(422, 'Something wrong.');
            }
        }
    }
}
