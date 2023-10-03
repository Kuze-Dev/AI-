<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAdditionalChargeData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Support\Str;

class CreateServiceOrderAction
{
    public function __construct(
        private CalculateServiceOrderTotalPriceAction $calculateServiceOrderTotalPriceAction,
    ) {
    }

    public function execute(ServiceOrderData $serviceData, int|null $adminId): ServiceOrder
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

        $customer = Customer::whereId($serviceData->customer_id)->first();

        $service = Service::whereId($serviceData->service_id)->first();

        $currency = Currency::whereEnabled(true)->first();

        $totalPrice = $this->calculateServiceOrderTotalPriceAction
            ->execute(
                $service->price,
                array_map(function ($additionalCharge) {
                    if (
                        isset($additionalCharge['price']) &&
                        is_numeric($additionalCharge['price']) &&
                        isset($additionalCharge['quantity']) &&
                        is_numeric($additionalCharge['quantity'])
                    ) {
                        return new ServiceOrderAdditionalChargeData(
                            $additionalCharge['price'],
                            $additionalCharge['quantity']
                        );
                    }
                }, $serviceData->additionalCharges)
            );

        $serviceOrder = ServiceOrder::create([
            'admin_id' => $adminId,
            'customer_id' => $serviceData->customer_id,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'customer_email' => $customer->email,
            'customer_mobile' => $customer->mobile,
            'customer_form' => $serviceData->form,
            'currency_code' => $currency->code,
            'currency_name' => $currency->name,
            'currency_symbol' => $currency->symbol,
            'service_address' => $serviceData->serviceAddress,
            'billing_address' => $customer->addresses->first()->address_line_1,
            'service_name' => $service->name,
            'service_price' => $service->price,
            'service_id' => $serviceData->service_id,
            'schedule' => $serviceData->schedule,
            'reference' => $uniqueReference,
            'status' => ServiceOrderStatus::PENDING,
            'additional_charges' => $serviceData->additionalCharges,
            'total_price' => $totalPrice,
        ]);

        return $serviceOrder;
    }
}
