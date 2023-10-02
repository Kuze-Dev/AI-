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

        $tenant = tenancy()->tenant;

        if ($tenant) {
           
            $today = now()->format('Y-m-d');

            /** @var \Domain\Tenant\Models\TenantApiCall|null */
            $tenantLog = $tenant->apiCalls()->where(
              'date', $today
            )->first();

            if ($tenantLog) {

                $tenantLog->update([
                    'count' => $tenantLog->count += 1
                ]);

            } else {
                $tenant->apiCalls()->create([
                    'date' => $today,
                    'count' => 1,
                ]);
            }
        }
        
    }
}