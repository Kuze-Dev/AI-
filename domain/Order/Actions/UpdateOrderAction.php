<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Media\Actions\CreateMediaAction;
use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Enums\OrderResult;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\Order;
use Domain\Payments\Actions\UploadProofofPaymentAction;
use Domain\Payments\DataTransferObjects\ProofOfPaymentData;
use Exception;
use Illuminate\Http\UploadedFile;

class UpdateOrderAction
{
    public function execute(Order $order, UpdateOrderData $updateOrderData): OrderResult|Exception
    {
        try {
            if ($updateOrderData->status) {
                if ($updateOrderData->status == 'For Cancellation') {
                    //cant cancel if order is
                    if ($order->status != OrderStatuses::PENDING) {
                        return OrderResult::FAILED;
                    }

                    $order->update([
                        'status' => $updateOrderData->status,
                        'cancelled_reason' => $updateOrderData->notes,
                    ]);
                } else {
                    $order->update([
                        'status' => $updateOrderData->status,
                        'cancelled_reason' => null,
                    ]);
                }
            }


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

            return OrderResult::SUCCESS;
        } catch (Exception $e) {
            \Log::info($e);
            return $e;
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
