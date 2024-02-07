<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\HttpTenantApi\Resources\ServiceBillResource;
use Domain\ServiceOrder\Actions\UpdateServiceBillProofOfPaymentAction;
use Domain\ServiceOrder\Actions\UpdateServiceOrderProofOfPaymentAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBankTransferData;
use Domain\ServiceOrder\Enums\Type;
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
        UpdateServiceBillProofOfPaymentAction $updateServiceBillProofOfPayment, UpdateServiceOrderProofOfPaymentAction $updateServiceOrderProofOfPaymentAction): mixed
    {
        try {
            $validatedData = $request->validated();

            if ($validatedData['type'] === Type::SERVICE_BILL->value) {
                $data = $updateServiceBillProofOfPayment->execute(ServiceBankTransferData::fromRequest($validatedData));
            } elseif ($validatedData['type'] === Type::SERVICE_ORDER->value) {
                $data = $updateServiceOrderProofOfPaymentAction->execute(ServiceBankTransferData::fromRequest($validatedData));
            } else {
                throw new BadRequestHttpException('Invalid Type');
            }

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
