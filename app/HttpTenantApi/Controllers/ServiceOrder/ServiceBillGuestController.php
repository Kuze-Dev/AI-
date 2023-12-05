<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\HttpTenantApi\Resources\ServiceBillGuestResource;
use Domain\ServiceOrder\Actions\ServiceBillGuestAction;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Requests\ServiceBillGuestStoreRequest;
use Spatie\RouteAttributes\Attributes\ApiResource;

#[
    ApiResource('service-bill-guest', only: ['store'])
]
class ServiceBillGuestController
{
    public function __construct(
        protected ServiceBillGuestAction $serviceBillGuestAction,
    ) {
    }

    public function store(ServiceBillGuestStoreRequest $request): ServiceBillGuestResource
    {
        $validated = $request->validated();

        $serviceBill = ServiceBill::whereReference($validated['reference'])->firstOrFail();

        $guestData = $this->serviceBillGuestAction->execute($serviceBill);

        return ServiceBillGuestResource::make($guestData);
    }
}
