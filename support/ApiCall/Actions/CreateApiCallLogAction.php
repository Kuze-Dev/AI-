<?php

declare(strict_types=1);

namespace Support\ApiCall\Actions;

use Support\ApiCall\Models\ApiCall;
use Support\ApiCall\DataTransferObjects\ApiCallData;

class CreateApiCallLogAction
{
    public function execute(ApiCallData $data): void
    {
        ApiCall::create([
            'request_url' => $data->requestUrl,
            'request_type' => $data->requestType,
            'request_response' => $data->requestResponse,
        ]);
    }
}