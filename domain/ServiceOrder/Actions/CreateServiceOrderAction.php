<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Address\Models\Address;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Support\Str;

class CreateServiceOrderAction
{
    public function __construct()
    {
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

        $customer = Customer::whereId($serviceData->customerId)->first();
        $service = Service::whereId($serviceData->serviceId)->first();
        $currency = Currency::whereEnabled(true)->first();
        $serviceAddressModel = Address::whereId($serviceData->serviceAddressId)->first();
        $serviceAddress = $serviceAddressModel->address_line_1 .' '. $serviceAddressModel->city .' '. $serviceAddressModel->state->name . ' ' .
                            $serviceAddressModel->state->country->name . ' ' . $serviceAddressModel->zip_code;
        $billingAddress = $serviceAddress;
        if( ! $serviceData->isSameAsBilling) {
            $billingAddressModel = Address::whereId($serviceData->billingAddressId)->first();
            $billingAddress = $billingAddressModel->address_line_1 .' '. $billingAddressModel->city .' '. $billingAddressModel->state->name . ' ' .
                            $billingAddressModel->state->country->name . ' ' . $billingAddressModel->zip_code;
        }
        $totalPrice = $service->price + array_reduce($serviceData->additionalCharges, function ($carry, $data) {
            if (isset($data['price']) && is_numeric($data['price']) && isset($data['quantity']) && is_numeric($data['quantity'])) {
                return $carry + ($data['price'] * $data['quantity']);
            }

            return $carry;
        }, 0);

        $serviceOrder = ServiceOrder::create([
            'admin_id' => $adminId,
            'service_id' => $serviceData->serviceId,
            'customer_id' => $serviceData->customerId,
            'customer_first_name' => $customer->first_name,
            'customer_last_name' => $customer->last_name,
            'customer_email' => $customer->email,
            'customer_mobile' => $customer->mobile,
            'customer_form' => $serviceData->form,
            'currency_code' => $currency->code,
            'currency_name' => $currency->name,
            'currency_symbol' => $currency->symbol,
            'service_address' => $serviceAddress,
            'billing_address' => $billingAddress,
            'service_name' => $service->name,
            'service_price' => $service->price,
            'schedule' => $serviceData->schedule,
            'reference' => $uniqueReference,
            'status' => ServiceOrderStatus::PENDING,
            'additional_charges' => $serviceData->additionalCharges,
            'total_price' => $totalPrice,
        ]);

        return $serviceOrder;
    }
}
