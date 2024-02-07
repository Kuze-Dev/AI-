<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Closure;
use Domain\Payments\Enums\PaymentRemark;
use Domain\Payments\Enums\PaymentStatus;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Events\AdminServiceOrderBankPaymentEvent;
use Domain\ServiceOrder\Models\ServiceOrder;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ServiceOrderBankTransferAction
{
    public function execute(array $data, ServiceOrder $record, Closure $set): void
    {
        DB::transaction(function () use ($data, $record, $set) {

            $paymentRemarks = $data['payment_remarks'];
            $message = $data['message'];

            /** @var \Domain\Payments\Models\Payment $payment */
            $payment = $record->latestPayment();

            if (is_null($paymentRemarks)) {
                Notification::make()
                    ->title(trans('No status found!'))
                    ->warning()
                    ->send();

                return;
            }

            $result = $payment->update([
                'remarks' => $paymentRemarks,
                'admin_message' => $message,
            ]);

            if (! $result) {
                Notification::make()
                    ->title(trans('Invalid Data!'))
                    ->danger()
                    ->send();

                return;
            }

            if ($paymentRemarks === PaymentRemark::DECLINED->value) {
                $payment->update([
                    'status' => 'pending',
                ]);

                $record->update([
                    'status' => ServiceOrderStatus::FORPAYMENT,
                ]);

                $set('status', ucfirst(ServiceOrderStatus::FORPAYMENT->value));
            }

            if ($paymentRemarks === PaymentRemark::APPROVED->value) {
                $payment->update([
                    'status' => PaymentStatus::PAID,
                ]);

                if ($record->serviceBills->first()?->status === ServiceBillStatus::PENDING) {
                    $record->update([
                        'status' => ServiceOrderStatus::FORPAYMENT,
                    ]);
                    $set('status', ucfirst(ServiceOrderStatus::FORPAYMENT->value));
                }

                Notification::make()
                    ->title(trans('Proof of payment updated successfully'))
                    ->success()
                    ->send();
                try {
                    event(new AdminServiceOrderBankPaymentEvent(
                        $record,
                        $paymentRemarks,
                        $payment
                    ));
                } catch (ModelNotFoundException $e) {
                    Notification::make()
                        ->title(trans($e->getMessage()))
                        ->danger()
                        ->send();
                }
            }

        });
    }
}
