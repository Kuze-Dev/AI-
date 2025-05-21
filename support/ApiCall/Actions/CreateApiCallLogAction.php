<?php

declare(strict_types=1);

namespace Support\ApiCall\Actions;

use Domain\Tenant\TenantSupport;
use Support\ApiCall\DataTransferObjects\ApiCallData;
use Support\ApiCall\Models\ApiCall;

class CreateApiCallLogAction
{
    public function execute(ApiCallData $data): void
    {
        if (! TenantSupport::initialized()) {
            return;
        }

        $tenant = TenantSupport::model();

        ApiCall::create([
            'request_url' => $data->requestUrl,
            'request_type' => $data->requestType,
            'request_response' => $data->requestResponse,
        ]);

        $today = now()->format('Y-m-d');

        /** @var \Domain\Tenant\Models\TenantApiCall|null */
        $tenantLog = $tenant->apiCalls()->where(
            'date',
            $today
        )->first();

        if ($tenantLog) {

            $tenantLog->update([
                'count' => $tenantLog->count += 1,
            ]);

        } else {
            $tenant->apiCalls()->create([
                'date' => $today,
                'count' => 1,
            ]);
        }

    }
}
