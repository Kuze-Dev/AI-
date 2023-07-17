<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Address\Actions\CreateAddressAction;
use Domain\Address\DataTransferObjects\AddressData;
use Domain\Customer\DataTransferObjects\CustomerRegisterData;
use Domain\Customer\Models\Customer;

class CustomerRegisterAction
{
    public function __construct(
        private readonly CreateCustomerAction $createCustomer,
        private readonly CreateAddressAction $createCustomerAddress,
    ) {
    }

    public function execute(CustomerRegisterData $customerRegisterData): Customer
    {
        $customer = $this->createCustomer->execute($customerRegisterData->customerData);

        $this->createCustomerAddress
            ->execute(
                AddressData::fromAddressAddCustomer(
                    $customer,
                    $customerRegisterData->shippingAddressData
                )
            );

        if ($customerRegisterData->billingAddressData !== null) {
            $this->createCustomerAddress
                ->execute(
                    AddressData::fromAddressAddCustomer(
                        $customer,
                        $customerRegisterData->billingAddressData
                    )
                );
        }

        return $customer;
    }
}
