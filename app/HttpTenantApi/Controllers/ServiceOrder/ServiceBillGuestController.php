<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\HttpTenantApi\Resources\ServiceBillResource;
use Domain\ServiceOrder\Actions\UpdateServiceBillProofOfPaymentAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBankTransferData;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Requests\ServiceBillGuestStoreRequest;
use Domain\ServiceOrder\Requests\UpdateServiceBillProofOfPaymentRequest;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Post;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[
    ApiResource('service-bill-guest', only: ['store'])
]
class ServiceBillGuestController
{
    public function store(ServiceBillGuestStoreRequest $request): ServiceBillResource
    {
        $validated = $request->validated();
        $serviceBill = ServiceBill::whereReference($validated['reference'])->firstOrFail();

        return ServiceBillResource::make($serviceBill);
    }

    #[Post('service-order/service-bills/banktransfer')]
    public function updateBankTransfer(UpdateServiceBillProofOfPaymentRequest $request,
        UpdateServiceBillProofOfPaymentAction $updateServiceBillProofOfPayment): mixed
    {
        try {
            $validatedData = $request->validated();

            $data = $updateServiceBillProofOfPayment->execute(ServiceBankTransferData::fromRequest($validatedData));

            return response([
                'message' => trans('Uploaded Successfully'),
                'data' => $data,
            ]);

        } catch (BadRequestHttpException $e) {
            return response(
                ['message' => trans($e->getMessage())],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
