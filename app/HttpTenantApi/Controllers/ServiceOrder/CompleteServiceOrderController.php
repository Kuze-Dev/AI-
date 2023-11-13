<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use Domain\ServiceOrder\Actions\UpdateServiceOrderStatusAction;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderStatusData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Exceptions\ServiceOrderNotFoundException;
use Domain\ServiceOrder\Exceptions\ServiceOrderNotYetPaidException;
use Domain\ServiceOrder\Requests\ServiceOrderCompletedRequest;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Symfony\Component\HttpFoundation\Response;

#[
    Prefix('service-order'),
    Middleware(['auth:sanctum'])
]
class CompleteServiceOrderController
{
    #[Post('complete')]
    public function __invoke(ServiceOrderCompletedRequest $request, UpdateServiceOrderStatusAction $updateServiceOrderStatusAction): mixed
    {
        try {
            $validated = $request->validated();

            $referenceId = $validated['reference_id'];

            $updateServiceOrderStatusAction->execute($referenceId,
                UpdateServiceOrderStatusData::fromRequest(ServiceOrderStatus::COMPLETED));

            return response()->json([
                'message' => 'Service Order Completed!',
            ], 200);
        } catch (ServiceOrderNotFoundException) {
            return response(
                ['message' => trans('Service order not found')],
                Response::HTTP_NOT_FOUND
            );
        } catch (ServiceOrderNotYetPaidException) {
            return response(
                ['message' => trans('Service Bill not yet paid')],
                Response::HTTP_NOT_FOUND
            );
        }

    }
}
