<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;

readonly class ImportCustomerAction
{
    public function __construct(
        private CreateCustomerAction $createCustomerAction,
        private EditCustomerAction $editCustomerAction,
    ) {
    }

    public function execute(array $row): Customer
    {
        $customer = Customer::whereEmail($row['email'])
            ->withTrashed()
            ->first();

        if ($customer !== null && $customer->trashed()) {
            return $customer;
        }

        $data = CustomerData::fromArrayImportByAdmin(
            customerPassword: $customer?->password,
            tierKey: isset($row['tier'])
                ? Tier::whereName($row['tier'])->first()?->getKey()
                : null,
            row: $row
        );

        unset($row);

        if ($customer === null) {
            return $this->createCustomerAction->execute($data);
        }

        return $this->editCustomerAction->execute($customer, $data);
    }
}
