<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Shipment\API\USPS\DataTransferObjects\ShippingRateData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;
use InvalidArgumentException;
use Throwable;
use Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse\ServiceData;

class GetUSPSRateAction
{
    public function __construct(
        private readonly GetShippingRateAction $getShippingRateAction,
    ) {
    }

    public function execute(
        Customer $customer,
        ParcelData $parcelData,
        ShippingMethod $shippingMethod,
        Address $address,
        ?int $service_id = null
    ): ShippingRateData {

        try {
            $data = $this->getShippingRateAction->execute(
                $customer,
                $parcelData,
                $shippingMethod,
                $address
            )->getRateResponseAPI();

            if ($data['is_united_state_domestic']) {

                return new ShippingRateData($data['package']->postage->rate);
            }

            $services = $data['package']->services;

            /** @var ServiceData $filteredServices */
            $filteredServices = array_filter($services, function ($service) use ($service_id) {
                return $service->id === $service_id;
            })['0'];

            return new ShippingRateData($filteredServices->postage);

        } catch (Throwable) {
            throw new InvalidArgumentException('Service Not Found');
        }

    }
}
