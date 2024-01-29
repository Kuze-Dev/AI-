<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use Domain\Payments\Exceptions\PaymentException;
use Domain\ServiceOrder\Actions\CheckoutServiceOrderAction;
use Domain\ServiceOrder\Actions\CheckoutServiceOrderPartialPaymentAction;
use Domain\ServiceOrder\DataTransferObjects\CheckoutServiceOrderData;
use Domain\ServiceOrder\Exceptions\PaymentExceedLimitException;
use Domain\ServiceOrder\Exceptions\ServiceBillAlreadyPaidException;
use Domain\ServiceOrder\Exceptions\ServiceOrderFullyPaidException;
use Domain\ServiceOrder\Exceptions\ServiceOrderStatusStillPendingException;
use Domain\ServiceOrder\Requests\ServiceTransactionStoreRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Symfony\Component\HttpFoundation\Response;

#[
    ApiResource('service-transaction', only: ['store']),
]
class ServiceOrderCheckoutController
{
    public function store(
        ServiceTransactionStoreRequest $request,
        CheckoutServiceOrderAction $checkoutServiceOrderAction,
        CheckoutServiceOrderPartialPaymentAction $checkoutServiceOrderPartialPaymentAction
    ): mixed {

        try {
            $validatedData = $request->validated();

            if (! array_key_exists('amount_to_pay', $validatedData)) {
                $data = $checkoutServiceOrderAction->execute(
                    CheckoutServiceOrderData::fromRequest($validatedData)
                );
            } else {
                $data = $checkoutServiceOrderPartialPaymentAction->execute(
                    CheckoutServiceOrderData::fromRequestPartial($validatedData)
                );
            }

            return response([
                'message' => trans('Proceed to payment'),
                'data' => $data,
            ]);
        } catch (ModelNotFoundException $m) {
            return response(
                ['message' => trans($m->getMessage())],
                Response::HTTP_NOT_FOUND
            );
        } catch (ServiceOrderStatusStillPendingException) {
            return response(
                ['message' => trans('Unable to proceed, service order\'s status is still on pending')],
                Response::HTTP_NOT_FOUND
            );
        } catch (PaymentException $p) {
            return response(
                ['message' => trans($p->getMessage())],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (PaymentExceedLimitException $p) {
            return response(
                ['message' => trans($p->getMessage())],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (ServiceOrderFullyPaidException $s) {
            return response(
                ['message' => trans($s->getMessage())],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (ServiceBillAlreadyPaidException) {
            return response(
                ['message' => trans('Service Bill already paid')],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
