<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\HttpTenantApi\Resources\ServiceBillResource;
use Domain\ServiceOrder\Actions\UpdateServiceBillProofOfPaymentAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillBankTransferData;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Requests\UpdateServiceBillProofOfPaymentRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('service-order/service-bills', only: ['show', 'update']),
]
class ServiceBillController
{
    public function show(string $serviceOrderRef): JsonApiResourceCollection
    {
        return ServiceBillResource::collection(
            QueryBuilder::for(ServiceBill::query()->whereServiceOrderRef($serviceOrderRef))
                ->defaultSort('-created_at')
                ->allowedIncludes(['serviceOrder'])
                ->allowedFilters(['status', 'reference'])
                ->allowedSorts(['reference', 'total_amount', 'status', 'created_at', 'due_date', 'bill_date'])
                ->jsonPaginate()
        );
    }

    #[Post('service-order/service-bills/banktransfer')]
    public function updateBankTransfer(UpdateServiceBillProofOfPaymentRequest $request,
        UpdateServiceBillProofOfPaymentAction $updateServiceBillProofOfPayment): mixed
    {
        try {
            $validatedData = $request->validated();

            $data = $updateServiceBillProofOfPayment->execute(ServiceBillBankTransferData::fromRequest($validatedData));

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
