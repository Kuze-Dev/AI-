<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;

class CreateServiceOrderAction
{
    public function __construct(

    ) {
    }

    public function execute(ServiceOrderData $serviceData): ServiceOrder
    {
        $customer = Customer::whereId($serviceData->customer_id)->first();
        $service = Service::whereId($serviceData->service_id)->first();
        $currency = Currency::whereEnabled(true)->first();
        $totalPrice = $service->price + array_reduce($serviceData->additionalCharges, function ($carry, $data) {
            if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                return $carry + ($data['price'] * $data['quantity']);
            }

            return $carry;
        }, 0);

        $serviceOrder = ServiceOrder::create([
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
            'status' => ServiceOrderStatus::PENDING,
            'additional_charges' => $serviceData->additionalCharges,
            'total_price' => $totalPrice,
        ]);

        return $serviceOrder;
    }
}
