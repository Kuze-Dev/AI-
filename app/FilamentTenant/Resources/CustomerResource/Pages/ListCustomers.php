<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Actions\CreateCustomerAction;
use Domain\Customer\Actions\EditCustomerAction;
use Domain\Customer\DataTransferObjects\CustomerData;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Models\Tier;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
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
                            'mobile' => $row['mobile'] ?? null,
                            'gender' => $row['gender'] ?? null,
                            'status' => $row['status'] ?? null,
                            'birth_date' => $row['birth_date'] ?? null,
                            'tier_id' => isset($row['tier'])
                                ? (Tier::whereName($row['tier'])->first()?->getKey())
                                : null,
                        ];
                        unset($row);

                        if ($customer = Customer::whereEmail($data['email'])->first()) {

                            return app(EditCustomerAction::class)
                                ->execute($customer, CustomerData::fromArrayImportByAdmin($data));
                        }

                        return app(CreateCustomerAction::class)
                            ->execute(CustomerData::fromArrayImportByAdmin($data));

                    }
                )
                ->withValidation(
                    rules: [
                        'email' => [
                            'required',
                            Rule::email(),
                        ],
                        'first_name' => 'required|string|max:100',
                        'last_name' => 'required|string|max:100',
                        'mobile' => 'nullable|min:3|max:100|regex:/^[0-9\s\p{P}\+\(\)]+$/u',
                        'gender' => ['nullable', Rule::enum(Gender::class)],
                        'status' => ['nullable', Rule::enum(Status::class)],
                        'birth_date' => 'nullable|date',
                        'tier' => [
                            'nullable',
                            Rule::exists(Tier::class, 'name'),
                        ],
                    ],
                ),
            ExportAction::make()
                ->model(Customer::class)
                ->queue()
                ->query(
                    fn (Builder $query) => $query
                        ->with('tier')
                        ->latest()
                )
                ->mapUsing(
                    ['CUID', 'Email', 'First Name',  'Last Name', 'Mobile', 'Gender', 'Status', 'Birth Date', 'Tier', 'Created At'],
                    fn (Customer $customer): array => [
                        $customer->cuid,
                        $customer->email,
                        $customer->first_name,
                        $customer->last_name,
                        $customer->mobile,
                        $customer->gender?->value,
                        $customer->status?->value,
                        $customer->birth_date?->format(config('tables.date_format')),
                        $customer->tier?->name,
                        $customer->created_at?->format(config('tables.date_time_format')),
                    ]
                ),
            Actions\CreateAction::make(),
        ];
    }
}
