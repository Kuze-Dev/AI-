<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Enums\OrderStatuses;
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
                if ($updateOrderData->status == 'Cancelled' && $order->status !== OrderStatuses::PENDING) {
                    return "You can't cancelled this order";
                }

                $orderData = [
                    'status' => $updateOrderData->status,
                ];

                if ($updateOrderData->status == 'Cancelled') {
                    $orderData['cancelled_reason'] = $updateOrderData->notes;
                } else {
                    $orderData['cancelled_reason'] = null;
                }

                $order->update($orderData);
            }

            if ($updateOrderData->type == "bank-transfer") {
                if ($updateOrderData->proof_of_payment !== null) {
                    $orderPayment = Order::with('payments')->find($order->id);

                    $test = $this->convertUrlToUploadedFile($updateOrderData->proof_of_payment);

                    app(UploadProofofPaymentAction::class)->execute(
                        $orderPayment->payments->first(),
                        new ProofOfPaymentData(
                            $test
                        )
                    );
                }
            } else {
                if ($updateOrderData->type != "status") {
                    $payment = Payment::whereHas('payable', function (Builder $query) use ($order) {
                        $query->wherePayableId($order->id);
                    })->whereNot('status', 'paid')->first();

                    if (!$payment) {
                        return 'Your order already paid';
                    }

                    $providerData = new CreatepaymentData(
                        transactionData: TransactionData::fromArray(
                            [
                                'reference_id' => $order->reference,
                                'amount' => AmountData::fromArray([
                                    'currency' => $order->currency_code,
                                    'total' => strval($order->total),
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

            return "Something went wrong";
        }
    }

    private function convertUrlToUploadedFile($url)
    {
        // Get the content of the file from the URL
        $fileContent = file_get_contents($url);

        // Create a temporary file path
        $tempFilePath = tempnam(sys_get_temp_dir(), 'upload');

        // Write the file content to the temporary file
        file_put_contents($tempFilePath, $fileContent);

        // Create an instance of UploadedFile using the temporary file
        $uploadedFile = new UploadedFile(
            $tempFilePath,
            basename($url),
            mime_content_type($tempFilePath),
            null,
            true
        );

        // Return the UploadedFile instance
        return $uploadedFile;
    }
}
