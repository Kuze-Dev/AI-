<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Events\OrderStatusUpdatedEvent;
use Domain\Order\Models\Order;
use Domain\Payments\Actions\CreatePaymentLink;
use Domain\Payments\Actions\UploadProofofPaymentAction;
use Domain\Payments\DataTransferObjects\AmountData;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\ProofOfPaymentData;
use Domain\Payments\DataTransferObjects\TransactionData;
use Domain\Payments\Models\Payment;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Log;

class UpdateOrderAction
{
    public function execute(Order $order, UpdateOrderData $updateOrderData): Order|string|PaymentAuthorize
    {
        try {
            if ($updateOrderData->status) {
                if (
                    $updateOrderData->status == OrderStatuses::CANCELLED->value &&
                    !in_array($order->status, [OrderStatuses::PENDING, OrderStatuses::FORPAYMENT])
                ) {
                    return "You can't cancelled this order";
                }

                if (
                    $updateOrderData->status == OrderStatuses::FULFILLED->value &&
                    $order->status !== OrderStatuses::DELIVERED
                ) {
                    return "You can't fullfilled this order";
                }

                $orderData = [
                    'status' => $updateOrderData->status,
                ];

                if ($updateOrderData->status == OrderStatuses::CANCELLED->value) {
                    $orderData['cancelled_reason'] = $updateOrderData->notes;
                } else {
                    $orderData['cancelled_reason'] = null;
                }

                $order->update($orderData);

                /** @var \Domain\Customer\Models\Customer $customer */
                $customer = auth()->user();

                event(new OrderStatusUpdatedEvent(
                    $customer,
                    $order,
                    $updateOrderData->status
                ));
            }

            if ($updateOrderData->type == 'bank-transfer' && $updateOrderData->proof_of_payment !== null) {
                /** @var \Domain\Order\Models\Order $orderPayment */
                $orderPayment = Order::with('payments')->find($order->id);

                $image = $this->convertUrlToUploadedFile($updateOrderData->proof_of_payment);

                /** @var \Domain\Payments\Models\Payment $payment */
                $payment = $orderPayment->payments->first();

                if ($image instanceof UploadedFile) {
                    if (
                        !empty($orderPayment->payments) &&
                        $payment->gateway == 'bank-transfer'
                    ) {
                        app(UploadProofofPaymentAction::class)->execute(
                            $payment,
                            new ProofOfPaymentData(
                                $image
                            )
                        );
                    } else {
                        return 'You cant upload a proof of payment in this gateway';
                    }
                } else {
                    return 'Invalid media';
                }
            } else {
                if ($updateOrderData->type != 'status') {
                    $payment = Payment::whereHas('payable', function (Builder $query) use ($order) {
                        $query->where('payable_id', $order->id);
                    })->whereNot('status', 'paid')->first();

                    if (!$payment) {
                        return 'Your order is already paid';
                    }

                    $providerData = new CreatepaymentData(
                        transactionData: TransactionData::fromArray(
                            [
                                'reference_id' => $order->reference,
                                'amount' => AmountData::fromArray([
                                    'currency' => $order->currency_code,
                                    'total' => (int) $order->total,
                                    'details' => PaymentDetailsData::fromArray(
                                        [
                                            'subtotal' => strval($order->sub_total - $order->discount_total),
                                            'tax' => strval($order->tax_total),
                                        ]
                                    ),
                                ]),
                            ]
                        ),
                        payment_driver: $updateOrderData->type
                    );

                    $result = app(CreatePaymentLink::class)->execute(
                        $payment,
                        $providerData
                    );

                    return $result;
                }
            }

            return $order;
        } catch (Exception $e) {
            // Log::info($e);

            return 'Something went wrong';
        }
    }

    private function convertUrlToUploadedFile(string $url): UploadedFile|string
    {
        $fileContent = file_get_contents($url);

        $tempFilePath = (string) tempnam(sys_get_temp_dir(), 'upload');

        if ($tempFilePath) {

            file_put_contents($tempFilePath, $fileContent);

            $mimeType = mime_content_type($tempFilePath) ?: 'application/octet-stream';

            // Create an instance of UploadedFile using the temporary file
            $uploadedFile = new UploadedFile(
                $tempFilePath,
                basename($url),
                $mimeType,
                null,
                true
            );

            return $uploadedFile;
        }

        return '';
    }
}
