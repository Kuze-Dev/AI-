<?php

declare(strict_types=1);

namespace Domain\Order\Actions\PublicOrder;

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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GuestUpdateOrderAction
{
    public function __construct(
        private readonly UploadProofofPaymentAction $uploadProofofPaymentAction,
        private readonly CreatePaymentLink $createPaymentLink,
    ) {
    }

    public function execute(
        Order $order,
        UpdateOrderData $updateOrderData
    ): Order|PaymentAuthorize {
        /** @var \Domain\Order\Models\Order $orderWithPayment */
        $orderWithPayment = $order->load('payments');

        if ($updateOrderData->status) {
            return $this->updateStatus(
                $orderWithPayment,
                $updateOrderData->status,
                $updateOrderData->notes
            );
        }

        if (
            $updateOrderData->type == 'bank-transfer' &&
            $updateOrderData->proof_of_payment !== null
        ) {
            $this->updateBankTransfer(
                $orderWithPayment,
                $updateOrderData->proof_of_payment,
                $updateOrderData->notes
            );
        } else {
            if ($updateOrderData->type != 'status') {
                return $this->updateWithGateway(
                    $orderWithPayment,
                    $updateOrderData->type
                );
            }
        }

        return $order;
    }

    private function updateStatus(Order $order, string $status, ?string $notes = null): Order
    {
        $orderData = [
            'status' => $status,
        ];

        if ($status == OrderStatuses::CANCELLED->value) {
            $orderData['cancelled_reason'] = $notes;
            $orderData['cancelled_at'] = now();

            /** @var \Domain\Payments\Models\Payment $payment */
            $payment = $order->payments->first();

            $payment->update([
                'status' => 'cancelled',
            ]);

            event(new OrderStatusUpdatedEvent(
                $order,
                $status
            ));
        } else {
            $orderData['cancelled_reason'] = null;
        }

        $order->update($orderData);

        return $order;
    }

    private function updateBankTransfer(
        Order $order,
        string $proofOfPayment,
        ?string $notes = null
    ): void {
        /** @var \Domain\Payments\Models\Payment $payment */
        $payment = $order->payments->first();

        if (
            $payment->gateway != 'bank-transfer'
        ) {
            throw new BadRequestHttpException('You cant upload a proof of payment in this gateway');
        }

        if (
            $order->status != OrderStatuses::FORPAYMENT
        ) {
            throw new BadRequestHttpException('Invalid action');
        }

        if (Str::contains($proofOfPayment, 'tmp/')) {
            if (Storage::disk('s3')->exists($proofOfPayment)) {

                $image = $this->convertUrlToUploadedFile($proofOfPayment);

                if ($image instanceof UploadedFile) {
                    $order->update([
                        'status' => OrderStatuses::FORAPPROVAL,
                    ]);

                    $payment->update([
                        'customer_message' => $notes,
                    ]);

                    $this->uploadProofofPaymentAction->execute(
                        $payment,
                        new ProofOfPaymentData(
                            $image
                        )
                    );
                } else {
                    throw new BadRequestHttpException('Invalid media');
                }
            }
        }
    }

    private function updateWithGateway(Order $order, string $gateway): PaymentAuthorize
    {
        $payment = Payment::whereHas('payable', function (Builder $query) use ($order) {
            $query->where('payable_id', $order->id);
        })->whereNot('status', 'paid')->first();

        if (! $payment) {
            throw new BadRequestHttpException('Your order is already paid');
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
            payment_driver: $gateway
        );

        $result = $this->createPaymentLink->execute(
            $payment,
            $providerData
        );

        return $result;
    }

    private function convertUrlToUploadedFile(string $url): UploadedFile|string
    {
        $fileContent = Storage::disk('s3')->get($url);

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
