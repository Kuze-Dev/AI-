<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Support\Str;

class CreateServiceOrderAction
{
    public function __construct(
        private CalculateServiceOrderTotalPriceAction $calculateServiceOrderTotalPriceAction,
        private CreateServiceOrderAddressAction $createServiceOrderAddressAction,
        private CreateServiceBillAction $createServiceBillAction,
    ) {
    }

    public function execute(ServiceOrderData $serviceOrderData, int|null $adminId): ServiceOrder
    {
        $uniqueReference = null;

        do {
            $referenceNumber = Str::upper(Str::random(12));

            $existingReference = ServiceOrder::where('reference', $referenceNumber)->first();

            if ( ! $existingReference) {
                $uniqueReference = $referenceNumber;

                break;
            }
        } while (true);

        $customer = Customer::whereId($serviceOrderData->customer_id)->first();

        $service = Service::whereId($serviceOrderData->service_id)->first();

        $currency = Currency::whereEnabled(true)->first();

        $totalPrice = $this->calculateServiceOrderTotalPriceAction
            ->execute(
                $service->selling_price,
                array_filter(
                    array_map(function ($additionalCharge) {
                        if (
                            isset($additionalCharge['price']) &&
                            is_numeric($additionalCharge['price']) &&
                            isset($additionalCharge['quantity']) &&
                            is_numeric($additionalCharge['quantity'])
                        ) {
                            return new ServiceOrderAdditionalChargeData(
                                (float) $additionalCharge['price'],
                                (int) $additionalCharge['quantity']
                            );
                        }
                    }, $serviceOrderData->additional_charges)
                )
            )
            ->getAmount();

        $serviceOrder = ServiceOrder::create([
            'admin_id' => $adminId,
            'service_id' => $serviceOrderData->service_id,
            'customer_id' => $serviceOrderData->customer_id,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'customer_email' => $customer->email,
            'customer_mobile' => $customer->mobile,
            'customer_form' => $serviceOrderData->form,
            'currency_code' => $currency->code,
            'currency_name' => $currency->name,
            'currency_symbol' => $currency->symbol,
            'service_name' => $service->name,
            'service_price' => $service->selling_price,
            'billing_cycle' => $service->billing_cycle,
            'due_date_every' => $service->due_date_every,
            'schedule' => $serviceOrderData->schedule,
            'reference' => $uniqueReference,
            'status' => ServiceOrderStatus::PENDING,
            'additional_charges' => $serviceOrderData->additional_charges,
            'total_price' => $totalPrice,
        ]);

        $this->createServiceOrderAddressAction->execute($serviceOrder, $serviceOrderData);

        // $this->createServiceBillAction->execute(ServiceBillData::fromArray($serviceOrder->toArray()));

        return $serviceOrder;
    }
}
