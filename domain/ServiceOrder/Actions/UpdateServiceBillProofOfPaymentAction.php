<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Payments\Actions\UploadProofofPaymentAction;
use Domain\Payments\DataTransferObjects\ProofOfPaymentData;
use Domain\ServiceOrder\DataTransferObjects\ServiceBankTransferData;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UpdateServiceBillProofOfPaymentAction
{
    public function __construct(
        private readonly UploadProofofPaymentAction $uploadProofofPaymentAction,
    ) {}

    public function execute(ServiceBankTransferData $serviceBankTransferData): ServiceBill
    {
        $serviceBill = ServiceBill::whereReference($serviceBankTransferData->reference_id)->firstOrFail();
        $payment = $serviceBill->latestPayment();

        if (! $payment) {
            throw new BadRequestHttpException('Payment not found!');
        }

        if ($payment->gateway !== 'bank-transfer') {
            throw new BadRequestHttpException('You cant upload a proof of payment in this gateway');
        }

        if (
            $serviceBill->status !== ServiceBillStatus::PENDING
        ) {
            throw new BadRequestHttpException('Invalid action');
        }

        $proofOfPayment = $serviceBankTransferData->proof_of_payment;

        if (! Storage::disk(config('filament.default_filesystem_disk'))->exists($proofOfPayment)) {
            throw new BadRequestHttpException('Image not found');
        }
        $image = $this->convertUrlToUploadedFile($proofOfPayment);

        if ($image instanceof UploadedFile) {
            $serviceBill->update([
                'status' => ServiceBillStatus::FOR_APPROVAL,
            ]);

            $payment->update([
                'customer_message' => $serviceBankTransferData->notes,
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

        return $serviceBill;
    }

    private function convertUrlToUploadedFile(string $url): UploadedFile|string
    {
        $fileContent = Storage::disk(config('filament.default_filesystem_disk'))->get($url);

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
