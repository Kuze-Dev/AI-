<?php

declare(strict_types=1);

namespace Domain\Customer\DataTransferObjects;

use Domain\Address\DataTransferObjects\AddressData;

class CustomerRegisterData
{
    public function __construct(
        public readonly CustomerData $customerData,
        public readonly AddressData $shippingAddressData,
        public readonly ?AddressData $billingAddressData,
    ) {
    }
}
