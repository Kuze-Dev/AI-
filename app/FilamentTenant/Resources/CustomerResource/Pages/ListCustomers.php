<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Actions\CreateCustomerAction;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Support\Excel\Actions\ExportAction;
use Support\Excel\Actions\ImportAction;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            ImportAction::make()
                ->model(Customer::class)
                ->processRowsUsing(
                    function (array $row): Customer {
                        $data = [
                            'email' => $row['email'],
                            'first_name' => $row['first_name'],
                            'last_name' => $row['last_name'],
                            'mobile' => $row['mobile'],
                            'status' => Status::from($row['status']),
                            'birth_date' => now()->parse($row['birth_date']),
                            'tier_id' => isset($row['tier']) ? (Tier::whereName($row['tier'])->first()?->getKey()) : null,
                        ];
                        unset($row);

                        if ($customer = Customer::whereEmail($data['email'])->first()) {
                            unset($data['email']);
                            $customer = app(EditCustomerAction::class)->execute($customer, new CustomerData(...$data));
                        } else {
                            $customer = app(CreateCustomerAction::class)->execute(new CustomerData(...$data));
                        }

                        return $customer;
                    }
                )
                ->withValidation(
                    rules: [
                        'email' => [
                            'required',
                            Rule::email(),
                        ],
                        'first_name' => 'required|string|min:3|max:100',
                        'last_name' => 'required|string|min:3|max:100',
                        'mobile' => 'required|string|min:3|max:100',
                        'status' => ['nullable', Rule::enum(Status::class)],
                        'birth_date' => 'required|date',
                        'tier' => [
                            'nullable',
                            Rule::exists(Tier::class, 'name'),
                        ],
                    ],
                ),
            ExportAction::make()
                ->model(Customer::class)
                ->queue()
                ->query(fn (Builder $query) => $query->with('tier')->latest())
                ->mapUsing(
                    ['CUID', 'Email', 'First Name',  'Last Name', 'Mobile', 'Status', 'Birth Date', 'Tier', 'Created At'],
                    fn (Customer $customer): array => [
                        $customer->cuid,
                        $customer->email,
                        $customer->first_name,
                        $customer->last_name,
                        $customer->mobile,
                        $customer->status->value,
                        $customer->birth_date->format(config('tables.date_format')),
                        $customer->tier?->name,
                        $customer->created_at?->format(config('tables.date_time_format')),
                    ]
                ),
            Actions\CreateAction::make(),
        ];
    }
}
