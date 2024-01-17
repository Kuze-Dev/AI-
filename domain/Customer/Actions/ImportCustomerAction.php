<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Enums\RegisterStatus;
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
        $data = [
            'email' => $row['email'],
            'first_name' => $row['first_name'] ?? '',
            'last_name' => $row['last_name'] ?? '',
            'mobile' => $row['mobile'] ? (string) $row['mobile'] : null,
            'gender' => $row['gender'] ?? null,
            //                            'status' => $row['status'] ?? null,
            'birth_date' => $row['birth_date'] ?? '',
            'tier_id' => isset($row['tier'])
                ? (Tier::whereName($row['tier'])->first()?->getKey())
                : null,
        ];
        unset($row);
        $customer = Customer::whereEmail($data['email'])->first();
        if ($customer?->register_status === RegisterStatus::REGISTERED) {
            $data['password'] = $customer->password;

            return $this->editCustomerAction
                ->execute($customer, CustomerData::fromArrayRegisteredImportByAdmin($data));
        }

        return $this->createCustomerAction
            ->execute(CustomerData::fromArrayRegisteredImportByAdmin($data));
    }
}
